<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Resources\v1\UserResource;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refreshToken']]);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Credenciales incorrectas'], 401);
            }

            $user = JWTAuth::user();
            $refreshToken = $this->createRefreshToken(JWTAuth::user());

            return response()->json([
                'success' => true,
                'data' => [
                    'access_token' => $token,
                    'refresh_token' => $refreshToken,
                    'user' => new UserResource($user),
                ]
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
                'success' => true,
                'data' => [
                    'access_token' => $newAccessToken,
                    'refresh_token' => $newRefreshToken,
                ]
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
                    'success' => false,
                    'message' => 'Token no proporcionado'
                ], 401);
            }

            JWTAuth::invalidate($token);

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar la sesión'
            ], 500);
        }
    }
}
