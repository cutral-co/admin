<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('cuit', 'password');

        $validator = Validator::make($credentials, [
            'cuit' => 'required|min:11|max:11',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return sendResponse(null, 'Credenciales invalidas', 400);
        }

        return $this->respondWithToken($token);
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

    public function get_user()
    {
        return response()->json(['user' => auth()->user()]);
    }

    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh());
    }

    protected function respondWithToken($token)
    {
        $data = [
            'user' => auth()->user(),
            'token' => $token,
        ];

        return sendResponse($data);
    }
}
