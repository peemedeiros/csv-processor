<?php

namespace App\Http\Controllers;

use App\Http\Requests\CSVImportRequest;
use App\Services\FileUpload\CsvImportService;
use Illuminate\Http\JsonResponse;

class FileController extends Controller
{
    public function __construct(protected CsvImportService $csvImportService)
    {
    }

    public function upload(CSVImportRequest $request): JsonResponse
    {
        $file = $request->file('file');

        $result = $this->csvImportService->upload($file);

        return response()->json([
            'message' => 'Importação iniciada com sucesso.',
            'data' => $result,
        ]);
    }
}
