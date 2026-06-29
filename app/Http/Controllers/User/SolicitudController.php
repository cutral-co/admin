<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

use App\Http\Resources\User\SolicitudResource;

use App\Mail\UerSolicitud\{EmailAprobacion, EmailConfirmacion, EmailRechazo};
use App\Models\Table\EstadoUserSolicitud;
use App\Models\User\Solicitud;

class SolicitudController extends Controller
{
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

    public function pendientes()
    {
        $solicitudes = Solicitud::whereNotNull('fecha_verificado')->where('estado_id', 1)->get();
        return sendResponse(SolicitudResource::collection($solicitudes));
    }

    public function aprobadas()
    {
        $solicitudes = Solicitud::where('estado_id', 2)->get();
        return sendResponse(SolicitudResource::collection($solicitudes));
    }

    public function rechazadas()
    {
        $solicitudes = Solicitud::where('estado_id', 3)->get();
        return sendResponse(SolicitudResource::collection($solicitudes));
    }

    public function cambiarEstado(Request $request)
    {
        $solicitud = Solicitud::find($request->id);
        if ($solicitud->estado_id == 2 || $solicitud->estado_id == 3) {
            return sendResponse(null, 'No se puede cambiar el estado de esta solicitud');
        }

        $solicitud->estado_id = $request->estado_id;
        $solicitud->save();

        /* confirmada */
        if ($solicitud->estado_id == 2) {
            Mail::to($solicitud->email)->send(new EmailAprobacion());
        }

        /* rechazada */
        if ($solicitud->estado_id == 3) {
            Mail::to($solicitud->email)->send(new EmailRechazo());
        }
        return sendResponse(new SolicitudResource($solicitud));
    }

    public function store(Request $request)
    {
        $body = $request->all();

        $cuit = $request->cuit;

        $estado_aprobado = EstadoUserSolicitud::where('value', 'aprobado')->first();
        $solicitud = Solicitud::where('cuit', $cuit)->where('estado_id', $estado_aprobado->id)->first();
        if ($solicitud) {
            return sendResponse(null, "Número de CUIT/CUIL ya se encuentra adherido a la factura digital");
        }

        $solicitud = Solicitud::where('cuit', $cuit)->whereNotNull('fecha_verificado')->where('estado_id', 1)->first();
        if ($solicitud) {
            return sendResponse(null, "Número de CUIT/CUIL ya tiene un correo electrónico activado");
        }

        $body['token_verificacion'] = uniqid();
        $solicitud = Solicitud::create($body);

        $link = env('APP_URL') . "verificar-correo?token=$solicitud->token_verificacion";

        try {
            Mail::to($solicitud->email)->send(new EmailConfirmacion($link));
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
            return redirect('http://www.cutralco.gob.ar/');
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
        $monitor = [
            'total' => Solicitud::all()->count(),
            'pendientes' => Solicitud::whereNotNull('fecha_verificado')->where('estado_id', 1)->count(),
            'aprobadas' => Solicitud::where('estado_id', 2)->count(),
            'rechazadas' => Solicitud::where('estado_id', 3)->count(),
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
            Mail::to($solicitud->email)->send(new EmailConfirmacion($link));
            $solicitud->ultimo_envio_email = \Carbon\Carbon::now();
            $solicitud->save();
        }
        return redirect()->away('http://www.cutralco.gob.ar/');
    }
}
