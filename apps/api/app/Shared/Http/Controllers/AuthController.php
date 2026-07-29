<?php

namespace App\Shared\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Login/logout del emprendedor (panel administrativo). No es un módulo de
 * dominio de negocio, por eso vive en Shared — ver arquitectura-backend.md.
 */
class AuthController
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son válidas.'],
            ]);
        }

        $token = $user->createToken('panel-admin')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'usuario' => [
                    'id' => $user->id,
                    'nombre' => $user->name,
                    'email' => $user->email,
                    'tenant_id' => $user->tenant_id,
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['data' => ['mensaje' => 'Sesión cerrada.']]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'nombre' => $user->name,
                'email' => $user->email,
                'tenant' => [
                    'id' => $user->tenant->id,
                    'nombre' => $user->tenant->nombre,
                ],
            ],
        ]);
    }
}
