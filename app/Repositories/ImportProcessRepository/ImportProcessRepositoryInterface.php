<?php

namespace App\Repositories\ImportProcessRepository;

use App\DTOs\DTO;
use App\Models\ImportProcess;

interface ImportProcessRepositoryInterface
{
    public function create(DTO $data): ImportProcess;
    public function getById(string|int $id): ?ImportProcess;
    public function update(int $id, array $data);
}
