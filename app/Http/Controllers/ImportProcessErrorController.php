<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImportProcessErrorCollection;
use App\Services\FileUpload\ImportProcessService;
use Illuminate\Http\Request;

class ImportProcessErrorController extends Controller
{
    public function __construct(protected ImportProcessService $importProcessService)
    {
    }

    public function __invoke(string $processId): ImportProcessErrorCollection
    {
        $ProcessErrors = $this->importProcessService->getProcessErrors($processId);

        return new ImportProcessErrorCollection($ProcessErrors);
    }
}
