<?php

namespace App\Repositories\UserRepository;

use App\DTOs\DTO;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function getAll(?int $paginate = null): LengthAwarePaginator|Collection;
    public function getById(int $id): User;
    public function findExistingEmails(array $emails): \Illuminate\Support\Collection;
    public function create(DTO $data): User;
    public function insert(array $data): bool;
}
