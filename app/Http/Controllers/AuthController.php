<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

use App\Http\Requests\User\LoginRequest;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('cuit', 'password');

        if (!JWTAuth::attempt($credentials)) {
            return sendResponse(null, 'Credenciales invalidas', 400);
        }

        $user = auth()->user();
        $user = User::where('id', $user->id)->with('person')->first();

        return sendResponse(UserResource::AuthWithToken($user));
    }

    public function get_user()
    {
        return response()->json(['user' => auth()->user()]);
    }

    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    public function logout(Request $request)
    {
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 200);
        }

        $token = $request->token;
        JWTAuth::invalidate($token);

        return response()->json([
            'status' => true,
            'message' => 'User has been logged out'
        ]);
    }
}
