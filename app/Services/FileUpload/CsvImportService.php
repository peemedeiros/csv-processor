<?php

namespace App\Services\FileUpload;

use App\Actions\CSV\ProcessCSVRow;
use App\DTOs\CreateImportProcessDTO;
use App\Enum\ImportProcessStatus;
use App\Jobs\ProcessCSVFile;
use App\Repositories\ImportProcessErrorRepository\ImportProcessErrorRepositoryInterface;
use App\Repositories\ImportProcessRepository\ImportProcessRepositoryInterface;
use App\Repositories\UserRepository\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvImportService
{
    public function __construct(
        protected ProcessCSVRow                         $processCsvRow,
        protected UserRepositoryInterface               $userRepository,
        protected ImportProcessRepositoryInterface      $importProcessRepository,
        protected ImportProcessErrorRepositoryInterface $importProcessErrorRepository,
    )
    {
    }

    public function upload(UploadedFile $file): array
    {
        $filename = date('Y-m-d_His') . '_' . Str::random(10) . '.csv';

        $importProcess = $this->importProcessRepository->create(
            (new CreateImportProcessDTO($file->getClientOriginalName(), ImportProcessStatus::PENDING))
        );

        $path = $file->storeAs('', $filename,'csv_imports');

        dispatch(new ProcessCSVFile(
            $path,
            $importProcess->id,
            Hash::make('123123123'),
            1000,
            $this->processCsvRow,
            $this->userRepository,
            $this->importProcessRepository,
            $this->importProcessErrorRepository
        ));

        return [
            'import_process_id' => $importProcess->id,
        ];
    }
}
