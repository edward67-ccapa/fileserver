<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadApiTest extends TestCase
{
    public function test_api_status_endpoint(): void
    {
        $response = $this->getJson('/api/upload');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'online',
                'service' => 'Image Server API & WebP Converter',
                'max_file_size' => '2MB (2048 KB)',
                'output_format' => 'webp',
            ]);
    }

    public function test_successful_image_upload_and_webp_conversion(): void
    {
        Storage::fake('public');

        // Create fake PNG file (10 KB size)
        $file = UploadedFile::fake()->create('farmaco.png', 10, 'image/png');

        $response = $this->postJson('/api/upload', [
            'empresa' => 'fombiopol',
            'descripcion' => 'madicamentos',
            'imagen' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'empresa' => 'fombiopol',
                    'descripcion' => 'madicamentos',
                    'original_name' => 'farmaco.png',
                    'filename' => 'farmaco.webp',
                    'path' => '/fombiopol/madicamentos/farmaco.webp',
                ]
            ]);

        // Check if converted webp file exists on disk
        $this->assertFileExists(public_path('uploads/fombiopol/madicamentos/farmaco.webp'));

        // Cleanup created file after test
        if (file_exists(public_path('uploads/fombiopol/madicamentos/farmaco.webp'))) {
            unlink(public_path('uploads/fombiopol/madicamentos/farmaco.webp'));
        }
    }

    public function test_upload_fails_when_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/upload', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Error de validación en la solicitud.',
            ])
            ->assertJsonValidationErrors(['empresa', 'descripcion', 'imagen']);
    }

    public function test_upload_fails_when_file_exceeds_2mb(): void
    {
        // Create fake file of 2049 KB (exceeding 2MB limit)
        $file = UploadedFile::fake()->create('heavy_image.jpg', 2049, 'image/jpeg');

        $response = $this->postJson('/api/upload', [
            'empresa' => 'fombiopol',
            'descripcion' => 'madicamentos',
            'imagen' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['imagen']);
    }
}
