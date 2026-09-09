<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImageConverterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageUploadController extends Controller
{
    protected ImageConverterService $imageConverter;

    public function __construct(ImageConverterService $imageConverter)
    {
        $this->imageConverter = $imageConverter;
    }

    /**
     * Display API info and status.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'service' => 'Image Server API & WebP Converter',
            'version' => '1.0.0',
            'max_file_size' => '2MB (2048 KB)',
            'output_format' => 'webp',
            'usage' => [
                'endpoint' => 'POST /api/upload',
                'body_type' => 'multipart/form-data',
                'parameters' => [
                    'empresa' => 'string (e.g. fombiopol)',
                    'descripcion' => 'string (e.g. madicamentos)',
                    'imagen' => 'file (max 2MB, formats: jpg, png, webp, gif, bmp)'
                ],
                'example_response_path' => '/fombiopol/madicamentos/farmaco.webp'
            ]
        ]);
    }

    /**
     * Upload image, convert to WebP, and save in organized folder structure.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'empresa' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string', 'max:100'],
            'imagen' => [
                'required',
                'file',
                'image',
                'max:2048' // Max 2MB (2048 KB)
            ],
        ], [
            'empresa.required' => 'El campo empresa es obligatorio.',
            'descripcion.required' => 'El campo descripcion es obligatorio.',
            'imagen.required' => 'El campo imagen es obligatorio.',
            'imagen.image' => 'El archivo adjunto debe ser una imagen válida.',
            'imagen.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif, webp, bmp, svg, tiff.',
            'imagen.max' => 'La imagen sobrepasa el tamaño máximo permitido de 2MB (2048 KB).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación en la solicitud.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $empresa = $request->input('empresa');
            $descripcion = $request->input('descripcion');
            $file = $request->file('imagen');

            $result = $this->imageConverter->convertAndSave($file, $empresa, $descripcion);

            return response()->json([
                'success' => true,
                'message' => 'Imagen procesada y convertida a WebP exitosamente',
                'data' => $result,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar la imagen.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
