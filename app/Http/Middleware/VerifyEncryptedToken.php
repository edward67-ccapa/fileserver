<?php

namespace App\Http\Middleware;

use App\Services\CryptoService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyEncryptedToken
{
    protected CryptoService $cryptoService;

    public function __construct(CryptoService $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    /**
     * Handle an incoming request and enforce encrypted CRUD token verification.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract token from header or request body
        $token = $request->header('X-Api-Token')
            ?? $request->bearerToken()
            ?? $request->input('token')
            ?? $request->input('auth_token');

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado: Se requiere un token de encriptación válido para realizar esta operación.',
                'error' => 'missing_encrypted_token',
                'hint' => 'Debe incluir el campo "token" en el form-data o el encabezado "X-Api-Token".'
            ], 403);
        }

        if (!$this->cryptoService->verifyCrudPermission($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado: El token de encriptación es inválido, ha sido alterado o no tiene permisos CRUD.',
                'error' => 'invalid_or_tampered_token',
            ], 403);
        }

        return $next($request);
    }
}
