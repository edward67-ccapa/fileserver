<?php

namespace Tests\Feature;

use App\Services\CryptoService;
use Tests\TestCase;

class CryptoServiceTest extends TestCase
{
    public function test_encrypt_and_decrypt_payload(): void
    {
        $crypto = new CryptoService();

        $originalData = [
            'permission' => 'crud',
            'user' => 'frontend_app',
            'granted' => true
        ];

        $token = $crypto->encrypt($originalData);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        $decrypted = $crypto->decrypt($token);
        $this->assertEquals($originalData, $decrypted);
    }

    public function test_verify_crud_permission_token(): void
    {
        $crypto = new CryptoService();

        $token = $crypto->encrypt(['permission' => 'crud', 'granted' => true]);
        $this->assertTrue($crypto->verifyCrudPermission($token));

        $invalidToken = 'invalid_corrupted_token_string';
        $this->assertFalse($crypto->verifyCrudPermission($invalidToken));
    }

    public function test_api_encrypt_endpoint(): void
    {
        $response = $this->postJson('/api/crypto/encrypt', [
            'data' => [
                'permission' => 'crud',
                'granted' => true
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Datos encriptados exitosamente',
            ]);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);

        // Test decrypting token via API endpoint
        $decryptResponse = $this->postJson('/api/crypto/decrypt', [
            'token' => $token
        ]);

        $decryptResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'decrypted' => [
                        'permission' => 'crud',
                        'granted' => true
                    ]
                ]
            ]);

        // Test verifying token via API endpoint
        $verifyResponse = $this->postJson('/api/crypto/verify', [
            'token' => $token
        ]);

        $verifyResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_permission' => true
            ]);
    }
}
