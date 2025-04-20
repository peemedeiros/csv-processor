<?php

namespace App\Repositories\ImportProcessErrorRepository;

use App\DTOs\DTO;
use App\Models\ImportProcessError;

class ImportProcessErrorRepositoryEloquent implements ImportProcessErrorRepositoryInterface
{

    public function create(DTO $data): ImportProcessError
    {
        return ImportProcessError::query()->create($data->toArray());
    }

    public function getById($id)
    {
        // TODO: Implement getById() method.
    }


    public function update(int $id, array $data): void
    {
        ImportProcessError::query()->where('id', $id)->update($data);
    }

    public function insert(array $data): bool
    {
        return ImportProcessError::query()->insert($data);
    }

    public function getProcessError(int|string $importProcessId, int $paginate = 20)
    {
        return ImportProcessError::query()->where('import_process_id', $importProcessId)->paginate($paginate);
    }
}
