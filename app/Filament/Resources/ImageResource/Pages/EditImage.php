<?php

namespace App\Filament\Resources\ImageResource\Pages;

use App\Filament\Resources\ImageResource;
use App\Models\Image;
use App\Services\ImageConverterService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditImage extends EditRecord
{
    protected static string $resource = ImageResource::class;

    protected static ?string $title = 'Editar Imagen';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Image $record) {
                    $filePath = public_path($record->full_path);
                    if (File::exists($filePath)) {
                        File::delete($filePath);
                    }
                }),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $converter = app(ImageConverterService::class);

        $oldFullPath = public_path($record->full_path);

        $empresa = $data['empresa'];
        $descripcion = $data['descripcion'];
        $tmpPath = $data['imagen_tmp'] ?? null;

        if (is_array($tmpPath)) {
            $tmpPath = reset($tmpPath);
        }

        // If a new image was uploaded
        if (!empty($tmpPath)) {
            $fullTmpPath = Storage::disk('public')->path($tmpPath);
            if (!file_exists($fullTmpPath)) {
                $fullTmpPath = storage_path('app/public/' . $tmpPath);
            }
            if (!file_exists($fullTmpPath)) {
                $fullTmpPath = storage_path('app/' . $tmpPath);
            }

            if (file_exists($fullTmpPath)) {
                $originalFilename = basename($tmpPath);
                $uploadedFile = new UploadedFile(
                    $fullTmpPath,
                    $originalFilename,
                    @mime_content_type($fullTmpPath) ?: 'image/jpeg',
                    null,
                    true
                );

                $result = $converter->convertAndSave($uploadedFile, $empresa, $descripcion);

                @unlink($fullTmpPath);

                // Delete old file if different
                if (File::exists($oldFullPath) && $oldFullPath !== public_path($result['full_path'])) {
                    File::delete($oldFullPath);
                }

                $record->update($result);
                return $record;
            }
        }

        // If no new image uploaded, but empresa or descripcion changed -> relocate file
        $empresaFolder = Str::slug($empresa);
        $descripcionFolder = Str::slug($descripcion);

        if ($empresaFolder !== $record->empresa || $descripcionFolder !== $record->descripcion) {
            $newRelativeFolderPath = $empresaFolder . '/' . $descripcionFolder;
            $newTargetDir = public_path('uploads/' . $newRelativeFolderPath);

            if (!File::exists($newTargetDir)) {
                File::makeDirectory($newTargetDir, 0755, true);
            }

            $newFullPath = $newTargetDir . '/' . $record->filename;
            $newRelativeWebPath = '/uploads/' . $newRelativeFolderPath . '/' . $record->filename;
            $newFormattedPath = '/' . $empresaFolder . '/' . $descripcionFolder . '/' . $record->filename;

            if (File::exists($oldFullPath)) {
                File::move($oldFullPath, $newFullPath);
            }

            $record->update([
                'empresa' => $empresaFolder,
                'descripcion' => $descripcionFolder,
                'path' => $newFormattedPath,
                'full_path' => $newRelativeWebPath,
                'url' => url($newRelativeWebPath),
            ]);
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
