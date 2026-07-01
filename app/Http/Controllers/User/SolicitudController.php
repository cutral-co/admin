<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

use App\Http\Resources\User\SolicitudResource;

use App\Mail\UerSolicitud\{EmailAprobacion, EmailConfirmacion, EmailPostConfirmacion, EmailRechazo};
use App\Models\DomicilioElectronico\Domicilio;
use App\Models\Person;
use App\Models\Table\EstadoUserSolicitud;
use App\Models\User\Solicitud;
use App\Models\User;
use App\Services\Email\EmailLogService;

class SolicitudController extends Controller
{
    public function __construct(private readonly EmailLogService $emailLogService) {}

    public function index(Request $request)
    {
        $solicitudes = Solicitud::all();
        return sendResponse($solicitudes);
    }

    public function get_by_token(Request $request)
    {
        $token = rtrim((string) $request->token, '/');
        $solicitud = Solicitud::where('token_verificacion', $token)->first();
        if ($solicitud) {
            return sendResponse(new SolicitudResource($solicitud));
        }
        return sendResponse(null, 'No se encontro la solicitud', 404);
    }

    public function porEstado(Request $request)
    {
        $estadoValue = $request->query('estado');
        if (!$estadoValue) {
            return sendResponse(null, 'El parametro estado es obligatorio', 422);
        }

        $estado = EstadoUserSolicitud::get($estadoValue);
        if (!$estado) {
            return sendResponse(null, 'Estado invalido. Use: nuevo, aprobado, rechazado', 422);
        }

        $query = Solicitud::with(['barrio', 'estado'])
            ->where('estado_id', $estado->id);

        if ($estado->value === 'nuevo') {
            $query->whereNotNull('fecha_verificado');
        }

        $solicitudes = $query->get();

        return sendResponse(SolicitudResource::collection($solicitudes));
    }

    public function cambiarEstado(Request $request)
    {
        try {
            DB::beginTransaction();

            $solicitud = Solicitud::find($request->id);
            if ($solicitud->estado_id == 2 || $solicitud->estado_id == 3) {
                DB::rollBack();
                return sendResponse(null, 'No se puede cambiar el estado de esta solicitud');
            }

            $solicitud->estado_id = $request->estado_id;
            $solicitud->save();

            if ($solicitud->estado_id == 2) {
                $plainPassword = $this->generateSecurePassword();

                $person = Person::updateOrCreate(
                    ['cuit' => $solicitud->cuit],
                    [
                        'name' => $solicitud->name,
                        'lastname' => $solicitud->lastname,
                        'email' => $solicitud->email,
                        'phone' => $solicitud->phone,
                        'calle' => $solicitud->calle,
                        'altura' => $solicitud->altura,
                        'manzana' => $solicitud->manzana,
                        'lote' => $solicitud->lote,
                        'piso' => $solicitud->piso,
                        'depto' => $solicitud->depto,
                        'barrio_id' => $solicitud->barrio_id,
                        'municipio' => $solicitud->municipio,
                        'otro_barrio' => $solicitud->otro_barrio,
                        'provincia_id' => $solicitud->provincia_id,
                    ],
                );

                $user = User::updateOrCreate(
                    ['cuit' => $person->cuit],
                    [
                        'password' => Hash::make($plainPassword),
                        'is_verified' => true,
                        'person_id' => $person->id,
                    ],
                );

                $domicilio = Domicilio::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'email' => $person->email,
                        'phone' => $person->phone,
                        'domicilio_real' => $person->stringDatosDomicilio(),
                        'nombre' => trim($person->name . ' ' . $person->lastname),
                        'documento' => $person->cuit,
                        'is_verified' => true,
                        'token' => null,
                    ],
                );

                $user->de_id = $domicilio->id;
                $user->save();

                DB::commit();

                $this->emailLogService->send(
                    $solicitud->email,
                    new EmailAprobacion($user->cuit, $plainPassword),
                    Solicitud::class,
                    $solicitud->id,
                    auth()->user()?->id,
                );

                return sendResponse(new SolicitudResource($solicitud->fresh(['barrio', 'estado'])));
            }

            if ($solicitud->estado_id == 3) {
                DB::commit();

                $this->emailLogService->send(
                    $solicitud->email,
                    new EmailRechazo(),
                    Solicitud::class,
                    $solicitud->id,
                    auth()->user()?->id,
                );

                return sendResponse(new SolicitudResource($solicitud->fresh(['barrio', 'estado'])));
            }

            DB::commit();

            return sendResponse(new SolicitudResource($solicitud->fresh(['barrio', 'estado'])));
        } catch (\Throwable $th) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    public function store(Request $request)
    {
        $body = $request->all();

        $cuit = $request->cuit;

        $estadoAprobado = EstadoUserSolicitud::get('aprobado');
        if ($estadoAprobado && Solicitud::where('cuit', $cuit)->where('estado_id', $estadoAprobado->id)->exists()) {
            return sendResponse(null, 'Numero de CUIT/CUIL ya se encuentra adherido a la factura digital');
        }

        $estadoNuevo = EstadoUserSolicitud::get('nuevo');
        if (
            $estadoNuevo
            && Solicitud::where('cuit', $cuit)
                ->whereNotNull('fecha_verificado')
                ->where('estado_id', $estadoNuevo->id)
                ->exists()
        ) {
            return sendResponse(null, 'Numero de CUIT/CUIL ya tiene un correo electronico activado');
        }

        $body['token_verificacion'] = uniqid();
        $solicitud = Solicitud::create($body);

        $link = env('APP_URL') . "verificar-correo?token=$solicitud->token_verificacion";

        try {
            $this->emailLogService->send(
                $solicitud->email,
                new EmailConfirmacion($link),
                Solicitud::class,
                $solicitud->id,
                auth()->user()?->id,
            );
            $solicitud->ultimo_envio_email = \Carbon\Carbon::now();
            $solicitud->save();
        } catch (\Throwable $th) {
            //return $th->getMessage();
            //throw $th;
        }

        return sendResponse($solicitud);
    }

    public function verificarCorreo(Request $request)
    {
        $token = rtrim((string) $request->query('token'), '/');
        $solicitud = Solicitud::where('token_verificacion', $token)->first();

        if (!$solicitud) {
            return redirect('http://www.cutralco.gob.ar/');
        }

        if ($solicitud->fecha_verificado) {
            $path = env('APP_CLIENT_URL') . "#/registro/verificacion?token=$solicitud->token_verificacion";
            return redirect($path);
        }

        $solicitud->fecha_verificado = \Carbon\Carbon::now();
        $solicitud->save();

        $path = env('APP_CLIENT_URL') . "#/registro/verificacion?token=$solicitud->token_verificacion";
        return redirect($path);
    }

    public function correoVerificado(Request $request)
    {
        return view('emailConfirmation');
    }

    public function monitor()
    {
        $estadoNuevo = EstadoUserSolicitud::get('nuevo');
        $estadoAprobado = EstadoUserSolicitud::get('aprobado');
        $estadoRechazado = EstadoUserSolicitud::get('rechazado');

        $monitor = [
            'total' => Solicitud::all()->count(),
            'pendientes' => $estadoNuevo
                ? Solicitud::whereNotNull('fecha_verificado')->where('estado_id', $estadoNuevo->id)->count()
                : 0,
            'aprobadas' => $estadoAprobado
                ? Solicitud::where('estado_id', $estadoAprobado->id)->count()
                : 0,
            'rechazadas' => $estadoRechazado
                ? Solicitud::where('estado_id', $estadoRechazado->id)->count()
                : 0,
            'sin_verificar' => Solicitud::whereNull('fecha_verificado')->count(),
        ];

        $solicitudesPorMes = Solicitud::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return sendResponse($solicitudesPorMes);
    }

    public function envio_correo_verificar()
    {
        $dosDiasAtras = \Carbon\Carbon::now()->subDays(2);

        $solicitudd = Solicitud::where('ultimo_envio_email', '<=', $dosDiasAtras)
            ->whereNull('fecha_verificado')
            ->get();

        foreach ($solicitudd as $solicitud) {
            $link = env('APP_URL') . "verificar-correo?token=$solicitud->token_verificacion";
            $this->emailLogService->send(
                $solicitud->email,
                new EmailConfirmacion($link),
                Solicitud::class,
                $solicitud->id,
                null,
            );
            $solicitud->ultimo_envio_email = \Carbon\Carbon::now();
            $solicitud->save();
        }
        return redirect()->away('http://www.cutralco.gob.ar/');
    }

    public function test_correo()
    {
        try {
            $link = env('APP_URL') . "verificar-correo?token=6a42d8c0c550b";

            $this->emailLogService->send(
                'gon.pineiro@gmail.com',
                new EmailConfirmacion($link),
            );
            return sendResponse('asdad');
        } catch (\Throwable $th) {
            return sendResponse(null, $th->getMessage());
        }
    }

    public function testCorreoTemplate(string $type)
    {
        if (!env('APP_DEBUG')) {
            return sendResponse(null, 'Endpoint disponible solo en entornos de debug', 403);
        }

        $link = env('APP_URL') . "verificar-correo?token=test-user-solicitud";

        try {
            $mailable = match ($type) {
                'confirmacion' => new EmailConfirmacion($link),
                'aprobacion' => new EmailAprobacion('20123456789', 'ABC123'),
                'rechazo' => new EmailRechazo(),
                'post-confirmacion' => new EmailPostConfirmacion(),
                default => null,
            };

            if (!$mailable) {
                return sendResponse(
                    null,
                    'Tipo de correo invalido. Use: confirmacion, aprobacion, rechazo, post-confirmacion',
                    422
                );
            }

            $emailLog = $this->emailLogService->send(
                'gon.pineiro@gmail.com',
                $mailable,
            );

            return sendResponse([
                'sent_to' => 'gon.pineiro@gmail.com',
                'type' => $type,
                'email_log_id' => $emailLog->id,
                'email_log_uuid' => $emailLog->uuid,
            ]);
        } catch (\Throwable $th) {
            return sendResponse(null, $th->getMessage(), 500);
        }
    }

    private function generateSecurePassword(int $length = 6): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }
}
