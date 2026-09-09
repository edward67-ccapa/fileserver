<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CryptoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CryptoController extends Controller
{
    protected CryptoService $cryptoService;

    public function __construct(CryptoService $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    /**
     * Encrypt a string or JSON payload using APP_KEY.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function encrypt(Request $request): JsonResponse
    {
        $payload = $request->input('data') ?? $request->all();

        if (empty($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'Proporcione el campo "data" para encriptar.'
            ], 422);
        }

        try {
            $token = $this->cryptoService->encrypt($payload);

            return response()->json([
                'success' => true,
                'message' => 'Datos encriptados exitosamente',
                'data' => [
                    'token' => $token,
                    'original' => $payload,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al encriptar datos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Decrypt an encrypted token using APP_KEY.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function decrypt(Request $request): JsonResponse
    {
        $token = $request->input('token') ?? $request->header('X-Api-Token');

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Proporcione el campo "token" para desencriptar.'
            ], 422);
        }

        try {
            $decrypted = $this->cryptoService->decrypt($token);

            return response()->json([
                'success' => true,
                'message' => 'Token desencriptado exitosamente',
                'data' => [
                    'decrypted' => $decrypted,
                    'token' => $token,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desencriptar token o firma inválida.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verify if a token grants valid CRUD permissions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        $token = $request->input('token') ?? $request->header('X-Api-Token');

        $isValid = $this->cryptoService->verifyCrudPermission($token);

        return response()->json([
            'success' => $isValid,
            'message' => $isValid
                ? 'El token es válido y otorga permisos CRUD.'
                : 'El token es inválido o no otorga permisos CRUD.',
            'has_permission' => $isValid,
        ], $isValid ? 200 : 403);
    }
}
