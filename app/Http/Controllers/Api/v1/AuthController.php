<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Credenciales incorrectas'], 401);
            }

            $refreshToken = $this->createRefreshToken(JWTAuth::user());

            return response()->json([
                'access_token' => $token,
                'refresh_token' => $refreshToken,
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token inválido o expirado'], 500);
        }
    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        $refreshToken = $request->validated()['refresh_token'];

        try {
            $user = JWTAuth::setToken($refreshToken)->authenticate();

            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            $newAccessToken = JWTAuth::fromUser($user);
            $newRefreshToken = $this->createRefreshToken($user);

            return response()->json([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token inválido o expirado'], 401);
        }
    }

    protected function createRefreshToken($user)
    {
        $refreshTTL = config('jwt.refresh_ttl');

        $payload = [
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + ($refreshTTL * 60),
        ];

        return JWTAuth::customClaims($payload)->fromUser($user);
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            if(! $token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token no proporcionado'
                ], 401);
            }

            JWTAuth::invalidate($token);

            return response()->json([
                'status' => 'success',
                'message' => 'Sesión cerrada exitosamente'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cerrar la sesión'
            ], 500);
        }
    }
}
