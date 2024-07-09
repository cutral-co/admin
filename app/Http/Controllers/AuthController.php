<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Validator, Hash};
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

use App\Http\Requests\User\{RegisterRequest, LoginRequest};
use App\Http\Resources\UserResource;
use App\Models\{Person, UserChange, User};
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(/* Register */Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::where('cuit', $request->cuit)->first();
            if ($user) {
                return sendResponse(null, 'Ya existe un usuario el número de CUIT ingresado', 300);
            }

            /* Persona */
            $person = Person::where('cuit', $request->cuit)->first();
            if (!$person) {
                $person = Person::where('email', $request->email)->first();
                if ($person) {
                    return sendResponse(null, 'Ya existe un usuario con el Correo electrónico ingresado', 300);
                }
                $personData = $request->only(['cuit', 'name', 'lastname', 'phone', 'email']);
                $person = Person::create($personData);
            } else {
                /* Buscamos si hay otra persona con el mismo correo */
                $other_person = Person::where('cuit', '!=', $request->cuit)
                    ->where('email', $request->email)
                    ->first();

                if ($other_person) {
                    return sendResponse(null, 'Ya existe un usuario con el Correo electrónico ingresado', 300);
                }

                $person->email = $request->email;
                $person->save();
            }

            /* Usuario */
            $userData = $request->only(['cuit', 'password']);
            $userData['password'] = Hash::make($request->password);
            $userData['person_id'] = $person->id;
            $user = User::create($userData);

            /* Registramos en user_changes y manejamos el token desde ahi */
            UserChange::create([
                'user_id' => $user->id,
                'type' => 'register',
                'token' => Str::random(60),
                'new_value' => $request->email,
            ]);

            DB::commit();

            return sendResponse($request->all());
        } catch (\Exception $e) {
            DB::rollBack();
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    public function activate_user(Request $request)
    {
        try {
            DB::beginTransaction();

            $user_change = UserChange::where('type', 'register')
                ->where('token', $request->token)
                ->first();

            if (!$user_change) {
                return redirect('https://google.com.ar');
            }

            $user_change->token = null;
            $user_change->save();

            User::where('id', $user_change->user_id)->first()->update(['is_verified' => true]);

            DB::commit();

            return sendResponse($user_change->new_value);
        } catch (\Exception $e) {
            DB::rollBack();
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('cuit', 'password');

        if (!JWTAuth::attempt($credentials)) {
            return sendResponse(null, 'Credenciales invalidas', 400);
        }

        if ($request->method == 'app_login') {
            return sendResponse(UserResource::Auth());
        }

        return sendResponse(UserResource::AuthWithApps());
    }

    public function refresh()
    {
        return sendResponse(UserResource::AuthWithApps());
    }

    public function logout(Request $request)
    {
        try {
            $validator = Validator::make($request->only('token'), [
                'token' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 200);
            }

            $token = $request->token;
            JWTAuth::invalidate($token);

            return sendResponse('Finalizo la sesión');
        } catch (\Throwable $th) {
            return sendResponse(null, 'Hubó un error para finalizar la sesión', 301);
        }
    }

    /*  */

    public function cambios_datos_usuario(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::where('id', $request->user_id)->first();
            if (!$user) {
                return sendResponse(null, 'No se encotro el usuario', 404);
            }

            $body = $request->except('user_id');

            if ($body['localidad'] === "SI") {
                $body['provincia_id'] = null;
                $body['municipio'] = null;
                $body['barrio'] = null;
            } else {
                $body['barrio_id'] = null;
            }

            $user->person->update($body);

            DB::commit();

            return sendResponse(UserResource::AuthWithApps());
        } catch (\Exception $e) {
            DB::rollBack();
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }
}
