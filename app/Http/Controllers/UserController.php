<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public static function get_user_info()
    {
        try {
            $id = auth()->user()->id;

            $user = User::where('id', $id)->first();
            if (!$user) {
                throw new Exception('No se encuentro el usuario con el id:' . $id);
            }

            $user->getAllPermissions();

            $response = self::getUserResponse($id);

            if (isset($response->message)) {
                return sendResponse(null, $response->message);
            }

            if ($response->error) {
                return sendResponse(null, $response->error);
            }

            $JWTAuth = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::class;

            $user_data = [
                'token' => \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::getToken(),
                ...$response->data['user_data'],
                ...$user->toArray(),
                'expires_in' => self::getExpiresIn(),
            ];

            return sendResponse($user_data);
        } catch (Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            log_send_response($log);
        }
    }

    public static function getUserResponse($id)
    {
        $url = env('BASE_ADMIN_URL') . 'private/get_user_info/' . $id;

        return (object) Http::withoutVerifying()->get($url)->json();
    }

    public static function get_persona()
    {
        $url = env('BASE_ADMIN_URL') . 'private/get_person_info';

        return (object) Http::withoutVerifying()->get($url)->json();
    }

    public static function get_user_id_by_dni($dni)
    {
        $url = env('BASE_WEB_LOGIN_API') . 'usuarioid/' . $dni;
        $json = (object) Http::withToken(env('TOKEN_USER_ID'))->get($url)->json();

        if (isset($json->message)) {
            throw new Exception($json->message);
        }

        /* Verificamos si hubo un error */
        if ($json->error) {
            throw new Exception($json->error);
        }

        if ($json->value[0]['usuarioID'] == 0) {
            throw new Exception('El documento no contiene un usuario en Mi Muni Digital');
        }

        return $json->value[0]['usuarioID'];
    }

    public static function get_user_id_by_email($email)
    {
        $url = env('BASE_ADMIN_URL') . 'private/get_user_by_email/' . $email;

        $response = (object) Http::withoutVerifying()->get($url)->json();

        if (!$response->data && !isset($response->data['id'])) {
            throw new Exception('Debera tener una cuenta en Mi Muni Digital o haber ingresado por primera vez');
        }

        return $response->data['id'];
    }
}
