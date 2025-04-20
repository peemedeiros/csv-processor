<?php

namespace App\Repositories\ImportProcessRepository;

use App\DTOs\DTO;
use App\Models\ImportProcess;

class ImportProcessRepositoryEloquent implements ImportProcessRepositoryInterface
{

    public function create(DTO $data): ImportProcess
    {
        return ImportProcess::query()->create($data->toArray());
    }

    public function getById(string|int $id): ?ImportProcess
    {
        return ImportProcess::query()->findOrFail($id);
    }

    public function update(int $id, array $data): void
    {
        ImportProcess::query()->where('id', $id)->update($data);
    }
}
