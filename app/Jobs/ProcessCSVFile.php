<?php

namespace App\Jobs;

use App\Actions\CSV\ProcessCSVRow;
use App\Enum\ImportProcessStatus;
use App\Helpers\ArrayHelper;
use App\Repositories\ImportProcessErrorRepository\ImportProcessErrorRepositoryInterface;
use App\Repositories\ImportProcessRepository\ImportProcessRepositoryInterface;
use App\Repositories\UserRepository\UserRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ProcessCSVFile implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 900;
    private int $usersCreatedCounter = 0;

    public function __construct(
        protected string                                $filePath,
        protected string                                $importProcessId,
        protected string                                $usersDefaultPassword,
        protected int                                   $chunkSize,
        protected ProcessCSVRow                         $processCsvRow,
        protected UserRepositoryInterface               $userRepository,
        protected ImportProcessRepositoryInterface      $importProcessRepository,
        protected ImportProcessErrorRepositoryInterface $importProcessErrorRepository,
    )
    {
    }

    public function handle(): void
    {
        try {
            /*
         * Atualiza processo de importação para PROCESSING
         */
            DB::transaction(function () {
                $this->importProcessRepository->update(
                    $this->importProcessId,
                    [
                        'status' => ImportProcessStatus::PROCESSING
                    ]
                );
            });

            /*
             * Define função de callback para realizar o save dos erros de validação de linha em lotes.
             */
            $this->processCsvRow->setErrorHandler(fn(array $errors) => $this->saveErrors($errors, $this->importProcessId));
            $this->processCsvRow->setDefaultPassword($this->usersDefaultPassword);

            /*
             * Realiza o processamento do arquivo em chunks para que possa suportar arquivos grandes.
             */
            foreach (ArrayHelper::chunkFile($this->filePath, $this->processCsvRow, $this->chunkSize) as $chunk) {
                DB::transaction(function () use ($chunk) {
                    $chunk = $this->validateUniqueEmailChunk($chunk);

                    if(empty($chunk)) {
                        return;
                    }

                    $this->usersCreatedCounter += count($chunk);
                    $this->userRepository->insert($chunk);

                    $this->importProcessRepository->update(
                        $this->importProcessId,
                        [
                            'processed_rows' => $this->processCsvRow->getValidCount(),
                            'users_created' => $this->usersCreatedCounter,
                            'failed_rows' => $this->processCsvRow->getInvalidCount(),
                        ]
                    );
                });
            }

            /*
             * Recupera erros de validação de linha restantes ao final do processamento.
             */
            $pendingErrors = $this->processCsvRow->flushErrors();
            if (!empty($pendingErrors)) {
                $this->saveErrors($pendingErrors, $this->importProcessId);
            }

            /*
             * Atualiza processo de importação para DONE, indicando o fim do processamento.
             */
            DB::transaction(function () {
                $this->importProcessRepository->update(
                    $this->importProcessId,
                    [
                        'status' => ImportProcessStatus::DONE,
                        'processed_rows' => $this->processCsvRow->getValidCount(),
                        'failed_rows' => $this->processCsvRow->getInvalidCount(),
                        'completed_at' => now()->toDateTimeString(),
                    ]
                );
            });

        } catch (\Exception $exception) {
            DB::transaction(function () use ($exception){
                $this->importProcessRepository->update(
                    $this->importProcessId,
                    [
                        'status' => ImportProcessStatus::FAILED,
                        'error_message' => $exception->getMessage(),
                    ]
                );
            });
        }
    }
    private function saveErrors(array $errors, int $importJobId): void
    {
        if (empty($errors)) {
            return;
        }

        $records = [];

        foreach ($errors as $error) {
            $records[] = [
                'import_process_id' => $importJobId,
                'row_data' => json_encode($error['row_data']),
                'error_message' => $error['error_message'],
                'error_type' => $error['error_type'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::transaction(function () use ($records) {
            $this->importProcessErrorRepository->insert($records);
        });
    }

    private function validateUniqueEmailChunk(array $chunk): array
    {
        $emails = array_map(fn($row) => $row['email'], $chunk);

        $existingUsers = $this->userRepository->findExistingEmails($emails);

        return array_filter($chunk, fn($row) => !in_array($row['email'], $existingUsers->toArray()));
    }

}
