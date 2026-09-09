<?php

namespace App\Filament\Resources\ImageResource\Pages;

use App\Filament\Resources\ImageResource;
use App\Models\Image;
use App\Services\ImageConverterService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CreateImage extends CreateRecord
{
    protected static string $resource = ImageResource::class;

    protected static ?string $title = 'Subir e Imagen a WebP';

    protected function handleRecordCreation(array $data): Image
    {
        $converter = app(ImageConverterService::class);

        $empresa = $data['empresa'];
        $descripcion = $data['descripcion'];
        
        $tmpPath = $data['imagen_tmp'] ?? null;
        if (is_array($tmpPath)) {
            $tmpPath = reset($tmpPath);
        }

        if (empty($tmpPath)) {
            throw new \InvalidArgumentException('No se recibió ningún archivo de imagen.');
        }

        // Locate uploaded temporary file path
        $fullTmpPath = Storage::disk('public')->path($tmpPath);
        if (!file_exists($fullTmpPath)) {
            $fullTmpPath = storage_path('app/public/' . $tmpPath);
        }
        if (!file_exists($fullTmpPath)) {
            $fullTmpPath = storage_path('app/' . $tmpPath);
        }

        if (!file_exists($fullTmpPath)) {
            throw new \RuntimeException('No se pudo encontrar el archivo temporal subido: ' . $tmpPath);
        }

        $originalFilename = basename($tmpPath);
        $uploadedFile = new UploadedFile(
            $fullTmpPath,
            $originalFilename,
            @mime_content_type($fullTmpPath) ?: 'image/jpeg',
            null,
            true
        );

        $result = $converter->convertAndSave($uploadedFile, $empresa, $descripcion);

        // Cleanup temporary file
        if (file_exists($fullTmpPath)) {
            @unlink($fullTmpPath);
        }

        // Return created Image model record
        return Image::where('full_path', $result['full_path'])->latest()->first()
            ?? Image::create($result);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
