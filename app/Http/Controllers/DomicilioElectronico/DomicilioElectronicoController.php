<?php

namespace App\Http\Controllers\DomicilioElectronico;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Http\Controllers\HttpClient\HttpController;
use App\Http\Controllers\HttpClient\WebLoginTrait;
use App\Http\Controllers\{LogController};
use App\Http\Resources\DomicilioElectronico\MensajeResource;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

use App\Models\DomicilioElectronico\{Domicilio, DomicilioNotificacion, Log, Notificacion, NotificacionArchivo, Origin, TipoDestinatario};
use App\Http\Requests\DomicilioElectronico\{DomicilioRequest, BusquedaPorDniRequest, CheckDomicilioOrigenRequest, VerificarDomicilioRequest, EnviarNotificacionRequest};
use App\Http\Requests\EnviarNotificacionDocuentoRequest;
use App\Jobs\SendDomicilioElectronicoMessage;
use App\Models\User;

class DomicilioElectronicoController extends \App\Http\Controllers\Controller
{
    use DomicilioElectronicoTrait;

    public function set_domicilio(DomicilioRequest $request)
    {
        try {
            $user = auth()->user();

            $domicilio = Domicilio::where('user_id', $user->id)->first();

            if ($domicilio) {
                return sendResponse($domicilio);
            }

            $userEmail = $user->person->email ?? null;
            $token = $userEmail && strtolower($userEmail) != strtolower($request->email)
                ? Str::random(60)
                : null;

            $isVerified = $token ? 0 : 1;

            $domicilio = Domicilio::create([
                'user_id' => $user->id,
                'email' => $request->email,
                'phone' => $request->phone,
                'domicilio_real' => $request->domicilio_real,
                'nombre' => $request->nombre,
                'documento' => $request->documento,
                'token' => $token,
                'is_verified' => $isVerified,
            ]);

            $user->de_id = $domicilio->id;
            $user->save();

            Log::create([
                'domicilio_id' => $domicilio->id,
                'message' => 'Se generó el Domicilio Electrónico',
            ]);

            return sendResponse($domicilio);
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    public function enviar_notificacion(EnviarNotificacionRequest $request)
    {
        try {
            $user = auth()->user();

            $domicilio = Domicilio::where('user_id', $user->id)->first();

            if (!$domicilio) {
                return sendResponse(null, 'El usuario no tiene domicilio electrónico configurado', 404);
            }

            $origin = Origin::where('name', $request->origin)->first();

            if (!$origin) {
                return sendResponse(null, 'El origen especificado no existe', 404);
            }

            $params = [
                'title' => $request->title,
                'body' => $request->body,
                'origin_id' => $origin->id,
                'data' => $request->data ?? null,
            ];

            $tipo_destinatario = TipoDestinatario::where('name', 'destinatario')->first();

            $domicilio_notificacion = $this->createDomicilioNotificacion(
                $params,
                $domicilio->id,
                $tipo_destinatario,
                $domicilio->documento
            );

            //$fechaFormateada = formatearFecha($domicilio_notificacion->fecha_recibido);
            $mensaje = "Usted ha sido notificado en su domicilio electrónico. Para poder ver dicha notificación deberá ingresar a <a href='https://t/#/login'>Cutral Digital</a><br><b>Fecha de notificación: </b> hs.";
            $subject = 'Nueva notificación electrónica - Municipalidad de Cutral Co';

            //sendEmail($domicilio->email, $subject, $mensaje);

            Log::create([
                'domicilio_id' => $domicilio->id,
                'message' => 'Notificación enviada exitosamente',
                'attributes' => json_encode([
                    'title' => $request->title,
                    'origin' => $request->origin,
                ]),
            ]);

            return sendResponse(new MensajeResource($domicilio_notificacion));
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    public function has_new_message()
    {
        try {
            $user = auth()->user();
            return sendResponse($user->domicilio_electronico_data);
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    public function notificaciones()
    {
        try {
            $user = auth()->user();

            $domicilio = Domicilio::where('user_id', $user->id)->first();

            return sendResponse(MensajeResource::collection($domicilio->mensajes));
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    public function set_view(Request $request)
    {
        try {
            DB::beginTransaction();
            $mensaje = DomicilioNotificacion::find($request->id);

            if ($mensaje->fecha_visto) {
                return sendResponse(null, "El mensaje ya tiene fecha visto", 300);
            }

            $mensaje->fecha_visto = \Carbon\Carbon::now();

            $mensaje->save();

            DB::commit();
            return sendResponse(new MensajeResource($mensaje));
        } catch (\Exception $e) {
            DB::rollBack();
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    public function set_archivado(Request $request)
    {
        try {
            DB::beginTransaction();
            $domicilio_notificacion = DomicilioNotificacion::find($request->id);

            if ($domicilio_notificacion->fecha_archivado) {
                return sendResponse(null, "El mensaje $domicilio_notificacion ya se encuentra archivado", 300);
            }

            $domicilio_notificacion->fecha_archivado = \Carbon\Carbon::now();

            $domicilio_notificacion->save();

            unset($domicilio_notificacion->user);

            DB::commit();
            return sendResponse(new MensajeResource($domicilio_notificacion));
        } catch (\Exception $e) {
            DB::rollBack();
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * @deprecated No funciona más
     */
    public function sendMessage(Request $request)
    {
        try {
            DB::beginTransaction();

            if ($request->token && $request->token != env('FOTOMULTA_TOKEN')) {
                return sendResponse(null, 'El Token es invalido', 403);
            }

            if (!$request->token) {
                $login = JWTAuth::attempt([
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

                if (!$login) {
                    return sendResponse(null, 'requiere autenticación', 403);
                }
            }

            $result = $this->validateOrResponse($request);
            if ($result instanceof JsonResponse) return $result;

            $notificacion = Notificacion::create([
                'title' => $request->title,
                'body' => $request->body,
                'data' => json_encode($request->data)
            ]);

            $files = $request->file('files');
            $file_paths = $this->saveArchivos($files);
            foreach ($file_paths as $file_path) {
                NotificacionArchivo::create(['path' => $file_path, 'type' => 'pdf', 'notificacion_id' => $notificacion->id]);
            }

            SendDomicilioElectronicoMessage::dispatch($result['destinatarios'], $result['representantes'], $notificacion);

            DB::commit();
            return sendResponse('Tareas de envío de mensajes en cola.');
        } catch (\Exception $e) {
            DB::rollBack();
            $log = LogController::save($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return sendResponse(null, "Ocurrió un error inesperado. Código: $log->id", 444);
        }
    }

    public function check_domicilio_origen(CheckDomicilioOrigenRequest $request)
    {
        try {
            $domicilios = Domicilio::where('documento', $request->documento)->get();

            if ($domicilios) {
                $response = $domicilios->map(function ($domicilio) {
                    return [
                        'domicilio_id' => $domicilio->id,
                        'email' => $domicilio->email
                    ];
                });
                return sendResponse($response);
            } else {
                return sendResponse(null, "No existen domicilios electrónicos para este documento.", 404);
            }
        } catch (\Exception $e) {
            LogController::save($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return sendResponse(null, $e->getMessage(), 401);
        }
    }

    public function getFile(Request $request)
    {
        if (isset($request->id)) {
            $archivo = NotificacionArchivo::obtenerArchivo($request->id);
            if (!$archivo) {
                return sendResponse(null, 'No se encuentra autorizado', 403);
            }
        }

        $file = getFileStorage($archivo->path);
        return sendResponse($file);
    }

    // Verifica si la persona tiene usuario en MMD
    public function tieneUsuarioMuni(BusquedaPorDniRequest $request)
    {
        try {
            $persona = WebLoginTrait::get_person_info($request->documento, $request->genero ?? '');

            // El genero es necesario
            if ($persona->value['error'] == 'Debe especificar género') {
                return sendResponse(null, ["message" => "Debe especificar genero", "code" => 3]);
            }

            // Error al consultar get_person_info
            if ($persona->value['error']) {
                return sendResponse(null, ["message" => $persona->value['error'], "code" => 0]);
            }

            $user_info = $persona->value['informacion'];

            // No existe el documento y genero ingresado
            if (!$user_info) {
                return sendResponse(null, ["message" => "No existe el usuario con el $request->documento y genero ingresado", "code" => 0]);
            }

            $user_id = $user_info['usuarioID'];

            // Chequeo de cuenta en MMD
            if ($user_id > 0) {
                return sendResponse("El documento $request->documento posee una cuenta en Muni Express");
            } else {
                return sendResponse(null, ["message" => "El documento $request->documento no posee una cuenta en Muni Express", "code" => 1]);
            }
        } catch (\Exception $e) {
            $log = LogController::save($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return sendResponse(null, ["message" => "Ocurrio un error inesperado. Codigo: $log->id"]);
        }
    }

    // Verificacion de correo electronico de DE sacando el token
    public function verificarDomicilioElectronico(VerificarDomicilioRequest $request)
    {
        try {
            $domicilio = Domicilio::where('email', $request->email)->where('token', $request->token)->first();
            if ($domicilio == null) {
                return sendResponse(null, 'No se ha podido validar el domicilio electrónico, alguno de los datos informados no concuerda.', 422);
            } else {
                $domicilio->token = null;
                $domicilio->is_verified = 1;
                $domicilio->save();
                return sendResponse('Domicilio electrónico verificado');
            }
        } catch (\Exception $e) {
            $log = LogController::save($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return sendResponse(null, ["message" => "Ocurrio un error inesperado. Codigo: $log->id"]);
        }
    }

    public function verificacion_domicilio_electronico(Request $request)
    {
        $response = HttpController::get_person_info($request->documento);

        $bool = isset($response->value) && $response->value && isset($response->value['informacion']) && $response->value['informacion'];

        if ($bool) {
            $user_id = $response->value["informacion"]["usuarioID"];

            if ($user_id == 0) {
                return sendResponse(null, 'No existe un usuario de Muni Express asociado a la identificacion', 404);
            }

            if ($dom = Domicilio::where('user_id', $user_id)->first()) {
                return sendResponse([
                    'documento' => $dom->documento,
                    'email' => $dom->email,
                    'phone' => $dom->phone
                ]);
            } else {
                return sendResponse(null, 'El usuario no tiene domicilio electrónico.', 404);
            }
        } else {
            return sendResponse(null, 'Problema para identificar al objeto', 404);
        }
    }

    public function updateDomicilio(DomicilioRequest $request)
    {
        try {
            $user = auth()->user();
            $user = User::find($user->id);
            $origin = Origin::where('name', 'mi-muni-digital')->first();
            $domicilio = Domicilio::where('user_id', $user->id)->first();
            if ($domicilio == null) {
                return sendResponse(null, 'No posee ningún domicilio guardado', 422);
            }
            //ya posee un domicilio
            DB::beginTransaction();

            $seEnviaraEmail = strtolower($domicilio->email) != strtolower($request->email) && strtolower($user->email) != strtolower($request->email);

            if ($seEnviaraEmail) {
                $domicilio->token = Str::random(60);
            } else {
                $domicilio->token = null;
                $domicilio->is_verified = 1;
            }

            $domicilio->email = $request->email;
            $domicilio->phone = $request->phone;
            $domicilio->domicilio_real = $request->domicilio_real;
            $domicilio->save();

            $domicilio->fresh();

            Log::create([
                'user_id' => $user->id,
                'domicilio_id' => $domicilio->id,
                'message' => 'Se actualizó Domicilio Electrónico',
            ]);

            $params = [
                'title' => "Actualización - Datos de contacto",
                'body' => "Usted actualizó los datos de contactos de su Domicilio Electrónico en Muni Express",
                'data' => ['ul' => [
                    "Email: $domicilio->email",
                    "Teléfono/Celular: $domicilio->phone",
                    "Domicilio Real: $domicilio->domicilio_real",
                    "Nombre: $domicilio->nombre",
                    "Documento: $domicilio->documento",
                    "Número de Trámite: $domicilio->renaper_id"
                ]],
                'origin_id' => $origin->id
            ];

            $tipo_destinatario = TipoDestinatario::where('name', 'destinatario')->first();
            $this->createDomicilioNotificacion($params, $domicilio->id, $tipo_destinatario);

            if ($seEnviaraEmail) {
                sendEmail($domicilio->email, 'Verificación de Email - Domicilio Electrónico', 'Presione el siguiente botón para poder verificar su domicilio electrónico', $domicilio->token);
            }

            DB::commit();

            return sendResponse($user->domicilio_electronico_data);
        } catch (\Exception $e) {
            DB::rollBack();
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    // Reenvia email de domicilio electronico de validacion
    public function reenviarEmail(Request $request)
    {
        $domicilio_electronico = Domicilio::where('email', $request->email)->first();
        if ($domicilio_electronico) {
            sendEmail($domicilio_electronico->email, 'Verificación de Email - Domicilio Electrónico', 'Presione el siguiente botón para poder verificar su domicilio electrónico', $domicilio_electronico->token);
        } else {
            return sendResponse(null, 'No se encontro Domicilio Electrónico');
        }
    }

    public function notificar_automaticamente2(Request $request)
    {
        try {
            $dependencia = $request->dependencia ?? 'MVD'; // * Debería venir siempre la dependencia pero se mantiene MVD por compatibilidad
            $infracciones = $request->only('actas');
            DB::beginTransaction();
            // $consulta_infraccion = $this->busqueda_por_infraccion($request->infraccion);

            $origin = Origin::where('name', 'sistema-multas')->first();

            $log['origin_id'] = $origin->id;
            if ($infracciones) {

                foreach ($infracciones['actas'] as $infraccion) {

                    $domicilio_electronico = Domicilio::find($infraccion['domicilio_notificacion_id']);

                    if (!$domicilio_electronico) {
                        $log['message'] = 'El usuario no tiene domicilio electronico.';
                        $log['user_id'] = $domicilio_electronico->user_id;
                        $log['attributes'] = json_encode(['domicilio_notificacion_id' => $domicilio_electronico->id, 'dni' => $infraccion['domicilio_notificacion_id']]);
                        Log::create($log);
                        DB::rollBack();
                        return sendResponse(null,  $log['message'], 404);
                    }

                    if ($infraccion['fecha_notificacion'] == null) {

                        $titulo = [
                            'MVD' => 'Infracción de tránsito - Monitoreo Vial Digital',
                            'MCD' => 'Infracción de tránsito - Monitoreo Ciudadano Digital',
                        ];

                        /* Paso todas las validaciones */
                        $params['title'] = $titulo[$dependencia];
                        $params['body'] = '-';
                        $params['data'] =  [
                            'ul' => [
                                "Número de acta: " . $dependencia . "-" . $infraccion['numero_infraccion'],
                                "Tipo de infracción: " . $infraccion['tipo'],
                                "Patente: " . $infraccion['plate'],
                                "Marca: " . $infraccion['marca_vehiculo'],
                                "Modelo: " . $infraccion['modelo_vehiculo'],
                                "Documento infractor: " . $infraccion['documento_titular'],
                                "Dirección de la infracción: " . $infraccion['direccion'],
                                "Fecha de la infracción: " . $infraccion['fecha_hora'] . " hs.",
                            ],
                            'link' => [
                                'label' => "PAGAR ONLINE",
                                'url' => "https://weblogin.neuquencapital.gov.ar/apps/pago-voluntario/#/pagar?numero_de_acta=" . $infraccion['numero_infraccion'] . "&dependencia=" . $dependencia . "&documento=" . $infraccion['documento_titular']
                            ],
                        ];
                        $params['origin_id'] = $origin->id;
                        $params['hash'] = uniqid();
                        $domicilio_notificacion = $this->createDomicilioNotificacion(
                            $params,
                            $domicilio_electronico->id,
                            TipoDestinatario::where('name', 'destinatario')->first(),
                        );

                        // envio de email
                        $fechaFormateada = formatearFecha($domicilio_notificacion->fecha_recibido);
                        $mensaje = "Usted ha sido notificado en su domicilio electrónico. Para poder ver dicha notificación deberá ingresar a <a href='https://weblogin.neuquencapital.gov.ar/#/login'>Muni Express</a><br><b>Fecha de notificación: </b> $fechaFormateada hs.";
                        $subject = 'Nueva notificación electrónica - Municipalidad de Neuquén';

                        $pdf = $infraccion['base64'];

                        $pdf = str_replace('data:application/pdf;base64,', '', $pdf);
                        $pdf = str_replace('dataapplication\/pdfbase64', '', $pdf);
                        $fileContent = base64_decode($pdf);

                        $a =  $this->saveArchivos([$fileContent], $domicilio_notificacion, 'pdf', 'ACTA ' . $dependencia . '-' . $infraccion['numero_infraccion']);
                        if (!isset($a)) {
                            DB::rollBack();
                            $log['message'] = 'No se guardo el archivo';
                            $log['user_id'] = $domicilio_electronico->user_id;
                            $log['attributes'] = json_encode(['domicilio_notificacion_id' => $domicilio_electronico->id, 'acta' => $infraccion['numero_infraccion']]);
                            Log::create($log);
                        }
                        if (env('APP_ENV') == 'production') {
                            sendEmail($domicilio_electronico->email, $subject, $mensaje);

                            // if ($request->email_aviso_notificacion) {
                            //     foreach ($request->email_aviso_notificacion as $email) {
                            //         sendEmail($email, 'Notificación Electrónica - Muni Express', 'Usted tiene una nueva notificacion en el Domicilio Electrónico');
                            //     }
                            // }
                        }
                        $respuesta[] = ['numero_infraccion' => $infraccion['numero_infraccion'], 'fecha_notificacion' =>  Carbon::now()->format('Y-m-d H:i:s')];
                    } else {
                        $respuesta[] = ['numero_infraccion' => $infraccion['numero_infraccion'], 'fecha_notificacion' => null];
                        // DB::rollBack();
                        // return sendResponse(null,  'El acta ya fue notificada', 404);
                    }
                }
            } else {
                DB::rollBack();
                $log['message'] = 'No hay datos en las infracciones';
                $log['attributes'] = json_encode(['response' => $infracciones]);
                Log::create($log);

                return sendResponse(null,  $log['message'], 404);
            }
            DB::commit();
            // [numero_infraccion=>123,fecha_notificacion=>fecha|null]
            // $notificacion_fecha = $this->cambiar_fecha_acta($datos_infraccion['numero_infraccion']);}


            return sendResponse($respuesta);
        } catch (\Exception $e) {
            DB::rollBack();
            $log['message'] = 'Error general';
            $log['attributes'] = json_encode($e->getTrace());
            Log::create($log);

            return sendResponse(null,  $e->getMessage(), 404);
        }
    }

    // Obtiene las infracciones por documento, se usa en el modulo MVD
    public function obtenerActasDocumento(Request $request)
    {
        try {
            $actas = $this->actasDocumento($request->documento);

            // Si el usuario tiene empresa asociada, se le agregan las actas de la misma
            $actas = $this->obtenerActasEmpresa($actas, $request->user_id);

            if ($actas && count($actas)) {

                foreach ($actas as $acta) {
                    $fechaVtoPrejudicial = $acta['fecha_vto_prejudicial'] ? Carbon::parse($acta['fecha_vto_prejudicial']) : null;

                    // $pdf = $this->fileToBase64($acta['get_acta_fisica']['path'] ?? $acta['get_acta_digital']['path']);
                    if ($acta['estado']['id'] != 11 && $fechaVtoPrejudicial && $fechaVtoPrejudicial->lt(Carbon::today())) {
                        $estado = 'Tribunal de Faltas';
                        $color = 'red';
                    } else {

                        if ($acta['estado']['id'] == 4) {
                            $estado = 'Notificada';
                            $color = 'yellow';
                        } else {
                            if ($acta['estado']['id'] == 11) {
                                $color = 'green';

                                $estado = 'Abonada';
                            } else {
                                $color = 'gray';
                                $estado = 'Proceso de notificación';
                            }
                        }
                    }
                    $fechaInfraccion = Carbon::createFromFormat('d/m/Y H:i', $acta['fecha_hora']);
                    $fechaNotificacion = Carbon::parse($acta['fecha_notificacion'])->format('d/m/Y');
                    $fechaAbonada = $acta['fecha_abonado'] ? Carbon::parse($acta['fecha_abonado'])->format('d/m/Y') : null;

                    $actasFront[] = [
                        'id' => $acta['id'],
                        'numero_infraccion' => $acta['numero_infraccion'],
                        'tipo' => $acta['tipo'],
                        'plate' => $acta['plate'],
                        'marca_vehiculo' => $acta['marca_vehiculo'],
                        'modelo_vehiculo' => $acta['modelo_vehiculo'],
                        'documento_titular' => $acta['documento_titular'],
                        'direccion' => $acta['direccion'],
                        'fecha_notificacion' => $fechaNotificacion,
                        'tabla_descuentos' => $acta['tabla_descuentos'],
                        'fecha_vto_prejudicial' => $fechaVtoPrejudicial ? $fechaVtoPrejudicial->format('d/m/Y') : null,
                        'fecha_abonado' => $fechaAbonada,
                        'fecha_hora' => $acta['fecha_hora'],
                        'is_voluntario' => $acta['is_voluntario'],
                        'fecha_infraccion' => $fechaInfraccion->format('Y-m-d H:i'),
                        'estado' => ['id' => $acta['estado']['id'], 'detalle' => $estado, 'color' => $color],
                        'link' => [
                            'label' => "PAGAR ONLINE",
                            'url' => "https://weblogin.neuquencapital.gov.ar/apps/pago-voluntario/#/pagar?numero_de_acta=" . $acta['numero_infraccion'] . "&dependencia=" . $acta['dependencia'] . "&documento=" . $acta['documento_titular']
                        ],
                        'archivo' => $acta['get_acta_digital']['path'] ?? $acta['get_acta_fisica']['path'],
                        'nombre_empresa' => $acta['nombre_empresa'] ?? null,
                    ];
                }
                $estadoOrden = ['Notificada', 'Proceso de notificación', 'Tribunal de Faltas', 'Abonada'];

                // Ordenar por estado personalizado y luego por fecha de infracción
                usort($actasFront, function ($a, $b) use ($estadoOrden) {
                    // Comparar por estado usando el orden definido en $estadoOrden
                    $estadoPosA = array_search($a['estado']['detalle'], $estadoOrden);
                    $estadoPosB = array_search($b['estado']['detalle'], $estadoOrden);

                    // Si los estados son diferentes, ordenar por el orden del array
                    if ($estadoPosA != $estadoPosB) {
                        return $estadoPosA - $estadoPosB;
                    }

                    // Si los estados son iguales, ordenar por fecha de infracción (descendente)
                    return strtotime($a['fecha_notificacion']) - strtotime($b['fecha_notificacion']);
                });

                return sendResponse($actasFront);
            } else {
                return sendResponse(null, 'No hay actas');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $log['message'] = 'Error al obtener las actas por doc';
            $log['attributes'] = json_encode($e->getTrace());
            Log::create($log);

            return sendResponse(null,  $e->getMessage(), 404);
        }
    }

    /**
     * Segun un user_id, se busca la empresa que tenga asociada
     * Si tiene, se buscan las infracciones por cuit de empresa y por dominios agregados manualmente
     * Se unen todas las actas y se filtran para eliminar las repetidas
     *
     * @param array $actas - Actas del usuario por su documento
     * @param int $user_id - ID del usuario
     *
     * @returns array Actas del usuario mergeadas con las de la empresa
     */
    private function obtenerActasEmpresa($actas, $user_id)
    {
        // Se busca el cuit de empresa asociado al usuario y si se encuentra se mergea sus actas
        $cuitEmpresa = $this->get_cuit_asociado_user_fm($user_id);

        if (isset($cuitEmpresa->value["successfull"])) {
            if ($cuitEmpresa->value["successfull"]) {
                $actasEmpresaCuit = [];
                $actasEmpresaDominio = [];

                // Obtiene infracciones de la empresa
                if (isset($cuitEmpresa->value["cuit"]) && $cuitEmpresa->value["cuit"] > 0) {
                    $actasEmpresaCuit = $this->actasDocumento($cuitEmpresa->value["cuit"]);
                }

                // Obtiene infracciones por dominios
                if (isset($cuitEmpresa->value["dominios"]) && !empty($cuitEmpresa->value["dominios"])) {
                    $actasEmpresaDominio = $this->actasDominios($cuitEmpresa->value["dominios"]);
                }

                // Agregar nombre de la empresa a cada acta
                $actasEmpresa = array_merge($actasEmpresaCuit, $actasEmpresaDominio);
                $nombreEmpresa = $cuitEmpresa->value["razonSocial"];

                foreach ($actasEmpresa as &$acta) {
                    $acta['nombre_empresa'] = $nombreEmpresa;
                }

                // Une todas las actas sin filtrar
                $actasUnificadas = array_merge($actasEmpresa, $actas);

                // Filtrar duplicados por numero_infraccion
                $actasSinDuplicados = [];
                foreach ($actasUnificadas as $acta) {
                    // Solo agrega el acta si su numero_infraccion no está ya en el array
                    $actasSinDuplicados[$acta['numero_infraccion']] = $acta;
                }

                // Convertir de nuevo a un array indexado
                $actas = array_values($actasSinDuplicados);
            }
        } else {
            LogController::save('Error al obtener el cuit asociado de una empresa al usuario: ' . $user_id, get_class() . '::' . __FUNCTION__, json_encode($cuitEmpresa->value));
        }

        return $actas;
    }

    // Obtiene el archivo pdf segun el path
    public function obtenerPdfActa(Request $request)
    {
        try {
            $pdf = $request->path;
            // $pdf = $this->rutaPdf($request->infraccion);
            if ($pdf) {
                $actaPdf = getFileStorage($pdf, 'fotomulta');
                return sendResponse($actaPdf);
            } else {
                return sendResponse(null, 'No llego el pdf', 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $log['message'] = 'Error al obtener las actas por doc';
            $log['attributes'] = json_encode($e->getTrace());
            Log::create($log);

            return sendResponse(null,  $e->getMessage(), 404);
        }
    }

    // Obtiene fotos y videos del acta para modulo MVD
    public function obtenerArchivosActa(Request $request)
    {
        try {
            $adjuntos = $this->adjuntosActa($request->infraccion);
            $adjuntos = $adjuntos['data'];
            // $pdf = $this->rutaPdf($request->infraccion);
            if ($adjuntos) {
                $archivos = [];
                foreach ($adjuntos['fotos'] as $key => $foto) {
                    $archivos[] = getFileStorage($foto['path'], 'fotomulta');
                }
                $archivos[] = getFileStorage($adjuntos['video']['path'], 'fotomulta');
                // $actaPdf = getFileStorage($pdf, 'fotomulta');
                return sendResponse($archivos);
            } else {
                return sendResponse(null, 'No hay archivos', 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $log['message'] = 'Error al obtener las actas por doc';
            $log['attributes'] = json_encode($e->getTrace());
            Log::create($log);

            return sendResponse(null,  $e->getMessage(), 404);
        }
    }

    // Se usa para generar constancia de notificacion
    public function obtenerDatosNotificacion(Request $request)
    {
        try {

            $notificacion = Notificacion::where('data', 'like', "%$request->numero_infraccion%")->with(['domicilio_notificacion.domicilio'])->first(); // * se borra MVD- en el like porque ahora se va a recibir directamente en el numero de infraccion para hacer dinamico

            if ($notificacion) {
                if (!$notificacion->hash) {
                    $notificacion->hash = uniqid();
                    $notificacion->save();
                }
                return sendResponse($notificacion);
            } else {
                return sendResponse(null, "No se encontro la notificación", 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $log['message'] = 'Error al obtener las actas por doc';
            $log['attributes'] = json_encode($e->getTrace());
            Log::create($log);

            return sendResponse(null,  $e->getMessage(), 404);
        }
    }

    public function checkDomicilioTelefono(Request $request)
    {
        try {
            $respuesta = null;
            $infoDomE = Domicilio::where('phone', $request->telefono)->first();

            if (!$infoDomE) {
                return sendResponse(null, 'No se encontro el domicilio electrónico', 404);
            }

            $infoDomE->makeHidden(['token']);
            $respuesta['dom_electronico'] = $infoDomE;
            $lic = $this->procesarDatosLicConducir($infoDomE->documento);
            $respuesta['lic_conducir'] = $lic->original['data'] ?? null;
            $infracciones = $this->getCantidadInfraccciones($infoDomE->documento);
            $respuesta['infracciones'] = $infracciones->data ?? null;


            return sendResponse($respuesta);
        } catch (\Exception $e) {
            DB::rollBack();
            $log['message'] = 'Error general';
            $log['attributes'] = json_encode($e->getTrace());
            Log::create($log);

            return sendResponse(null,  $e->getMessage(), 404);
        }
    }

    public function enviar_notificacion_obras(EnviarNotificacionDocuentoRequest $request)
    {
        $notificacion = json_decode($this->enviar_notificacion_documento($request));
        $notificacion = $notificacion->data;
        $nombreArchivo = 'FORMULARIO PAGO ANTICIPADO.pdf';
        $rutaOrigen = public_path($nombreArchivo);
        $rutaDestino = 'domicilio_electronico/' . $nombreArchivo;

        if (!Storage::disk('serverdata')->exists($rutaDestino)) {
            if (!file_exists($rutaOrigen)) {
                throw new \Exception("Archivo no encontrado: $rutaOrigen");
            }
            $contenido = file_get_contents($rutaOrigen);
            Storage::disk('serverdata')->put($rutaDestino, $contenido);
        }

        NotificacionArchivo::create([
            'name' => 'FORMULARIO PAGO ANTICIPADO',
            'path' => $rutaDestino,
            'notificacion_id' => $notificacion->id,
        ]);
    }

    public function enviar_notificacion_origen_html(EnviarNotificacionDocuentoRequest $request)
    {
        try {
            $origin = Origin::where('name', $request->origin)->first();

            $documento = $request->input('documento');

            $domicilioElectronico = Domicilio::where('documento', $documento)->first();

            /* Paso todas las validaciones */
            $params = $request->only(['title', 'body']);
            $params['origin_id'] = $origin->id;

            $domicilio_notificacion = $this->createDomicilioNotificacionAviso(
                $params,
                $domicilioElectronico
            );

            $this->saveArchivos($request->file('files'), $domicilio_notificacion);

            return sendResponse(new MensajeResource($domicilio_notificacion));
        } catch (\Exception $e) {
            $log['message'] = 'Error general';
            $log['attributes'] = json_encode($e->getTrace());
            $log['origin_id'] = $origin->id;

            Log::create($log);
            return sendResponse(null,  $e->getMessage(), 404);
        }
    }

    public function eliminarNotificacionPorActa(Request $request)
    {
        $domicilio_id = $request->domicilio_id;
        $numero_acta = $request->numero_acta; // Ejemplo: MVD-123

        // Solo se agrega la última comilla al final
        $busqueda = '"Número de acta: ' . $numero_acta . '"';

        $notificacion = Notificacion::whereHas('domicilio_notificacion', function ($q) use ($domicilio_id) {
            $q->where('domicilio_id', $domicilio_id);
        })
            ->where('data', 'like', "%$busqueda%")
            ->first();

        if ($notificacion) {
            $notificacion->deleted_at = now();
            $notificacion->save();
            return sendResponse("Notificación eliminada correctamente", null, 200);
        } else {
            return sendResponse(null, "No se encontró la notificación", 404);
        }
    }

    // ============================================================
    // ADMIN / BACKOFFICE ENDPOINTS
    // ============================================================

    /**
     * Obtiene todos los domicilios electrónicos NO verificados (is_verified = 0).
     * GET /api/admin/domicilio-electronico/pendientes
     */
    public function getPendientes()
    {
        try {
            $domicilios = Domicilio::where('is_verified', 0)
                ->orderBy('created_at', 'desc')
                ->get()
                ->makeVisible('id')
                ->makeHidden([
                    'token',
                    'updated_at',
                    'mensajes',
                    'has_notificaciones',
                    'countNotificacionesSinVer',
                ]);

            return sendResponse($domicilios);
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * Verifica un domicilio electrónico manualmente desde el backoffice.
     * POST /api/admin/domicilio-electronico/verificar
     * Body: { id: int }
     */
    public function verificarDomicilio(Request $request)
    {
        try {
            $request->validate(['id' => 'required|integer|exists:de_domicilio,id']);

            $domicilio = Domicilio::find($request->id);

            if ($domicilio->is_verified) {
                return sendResponse(null, 'El domicilio electrónico ya está verificado', 300);
            }

            $domicilio->is_verified = 1;
            $domicilio->token = null;
            $domicilio->save();

            Log::create([
                'domicilio_id' => $domicilio->id,
                'message' => 'Domicilio electrónico verificado manualmente desde el backoffice',
            ]);

            return sendResponse($domicilio
                ->makeVisible('id')
                ->makeHidden([
                    'token',
                    'updated_at',
                    'mensajes',
                    'has_notificaciones',
                    'countNotificacionesSinVer',
                ]));
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * Obtiene las últimas 500 notificaciones enviadas (admin).
     * GET /api/admin/domicilio-electronico/notificaciones
     */
    public function getNotificacionesAll()
    {
        try {
            $notificaciones = Notificacion::with(['origin', 'domicilio_notificacion.domicilio'])
                ->orderBy('created_at', 'desc')
                ->limit(500)
                ->get()
                ->map(function ($notif) {
                    return [
                        'id' => $notif->id,
                        'origen' => $notif->origin ? $notif->origin->descripcion : 'Sin origen',
                        'titulo' => $notif->title,
                        'mensaje' => $notif->body,
                        'leida' => $notif->domicilio_notificacion && $notif->domicilio_notificacion->fecha_visto ? true : false,
                        'created_at' => $notif->created_at ? $notif->created_at->format('Y-m-d H:i') : null,
                    ];
                });

            return sendResponse($notificaciones);
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * Busca un contribuyente por CUIT/documento.
     * GET /api/admin/domicilio-electronico/buscar-cuit/{cuit}
     */
    public function buscarContribuyente(string $cuit)
    {
        try {
            $domicilio = Domicilio::where('documento', $cuit)->first();

            if (!$domicilio) {
                return sendResponse(null, 'No se encontró un domicilio electrónico para el CUIT ingresado', 404);
            }

            $domicilio->makeVisible('id')->makeHidden([
                'token',
                'updated_at',
                'mensajes',
                'has_notificaciones',
                'countNotificacionesSinVer',
            ]);

            return sendResponse($domicilio);
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * Obtiene todos los orígenes disponibles.
     * GET /api/admin/domicilio-electronico/origenes
     */
    public function getOrigenes()
    {
        try {
            $origenes = Origin::all()->makeHidden(['token']);
            return sendResponse($origenes);
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * Envía una notificación desde el backoffice a un contribuyente.
     * POST /api/admin/domicilio-electronico/enviar
     * Body: { title, body, origin, documento }
     */
    public function enviarNotificacionAdmin(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required|string',
                'origin' => 'required|string',
                'documento' => 'required|string|size:11',
            ]);

            // Buscar el origen por nombre
            $origin = Origin::where('name', $request->origin)->first();
            if (!$origin) {
                return sendResponse(null, 'El origen especificado no existe', 404);
            }

            // Buscar el domicilio por documento
            $domicilio = Domicilio::where('documento', $request->documento)->first();
            if (!$domicilio) {
                return sendResponse(null, 'No se encontró un domicilio electrónico para el documento ingresado', 404);
            }

            if (!$domicilio->is_verified) {
                return sendResponse(null, 'El domicilio electrónico del contribuyente no está verificado', 400);
            }

            $params = [
                'title' => $request->title,
                'body' => $request->body,
                'origin_id' => $origin->id,
                'data' => $request->data ?? null,
            ];

            $tipo_destinatario = TipoDestinatario::where('name', 'destinatario')->first();

            $domicilio_notificacion = $this->createDomicilioNotificacion(
                $params,
                $domicilio->id,
                $tipo_destinatario,
                $domicilio->documento
            );

            Log::create([
                'domicilio_id' => $domicilio->id,
                'notificacion_id' => $domicilio_notificacion->notificacion_id,
                'message' => 'Notificación enviada desde el backoffice',
                'attributes' => json_encode([
                    'title' => $request->title,
                    'origin' => $request->origin,
                    'enviado_por_admin' => true,
                ]),
            ]);

            return sendResponse(new MensajeResource($domicilio_notificacion));
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * Obtiene todos los domicilios electrónicos VERIFICADOS.
     * GET /api/admin/domicilio-electronico/domicilios
     */
    public function getDomiciliosVerificados()
    {
        try {
            $domicilios = Domicilio::where('is_verified', 1)
                ->orderBy('created_at', 'desc')
                ->get()
                ->makeVisible('id')
                ->makeHidden([
                    'token',
                    'updated_at',
                    'mensajes',
                    'has_notificaciones',
                    'countNotificacionesSinVer',
                ]);

            return sendResponse($domicilios);
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }
}
