<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImportProcessResource;
use App\Repositories\ImportProcessRepository\ImportProcessRepositoryInterface;
use App\Services\FileUpload\ImportProcessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportProcessController extends Controller
{
    public function __construct(protected ImportProcessService $importProcessService)
    {
    }

    public function __invoke(string $processId): JsonResponse
    {
        $importProcess = $this->importProcessService->getProcess($processId);

        return response()->json([
            'message' => 'Processo de importação recuperado com sucesso.',
            'data' => new ImportProcessResource($importProcess),
        ]);
    }
}
