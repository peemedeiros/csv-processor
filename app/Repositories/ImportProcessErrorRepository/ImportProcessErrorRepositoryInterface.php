<?php

namespace App\Repositories\ImportProcessErrorRepository;

use App\DTOs\DTO;

interface ImportProcessErrorRepositoryInterface
{
    public function create(DTO $data);
    public function insert(array $data);
    public function getById($id);
    public function update(int $id, array $data);

    public function getProcessError(string|int $importProcessId, int $paginate = 20);
}
