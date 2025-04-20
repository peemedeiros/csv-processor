<?php

namespace App\Services\FileUpload;

use App\Models\ImportProcess;
use App\Repositories\ImportProcessErrorRepository\ImportProcessErrorRepositoryInterface;
use App\Repositories\ImportProcessRepository\ImportProcessRepositoryInterface;

class ImportProcessService
{
    public function __construct(
        protected ImportProcessRepositoryInterface $importProcessRepository,
        protected ImportProcessErrorRepositoryInterface $importProcessErrorRepository,
    )
    {
    }

    public function getProcess(string $processId): ?ImportProcess
    {
        return $this->importProcessRepository->getById($processId);
    }

    public function getProcessErrors(string $processId)
    {
        return $this->importProcessErrorRepository->getProcessError($processId);
    }
}
