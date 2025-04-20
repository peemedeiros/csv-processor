<?php

namespace App\Repositories\UserRepository;

use App\DTOs\DTO;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepositoryEloquent implements UserRepositoryInterface
{
    public function getAll(?int $paginate = null): LengthAwarePaginator|Collection
    {
        if (is_int($paginate)) {
            return User::query()->paginate($paginate);
        }
        return User::query()->get();
    }

    public function getById(int $id): User
    {
        return User::query()->find($id);
    }

    public function findExistingEmails(array $emails): \Illuminate\Support\Collection
    {
        return User::query()
            ->whereIn('email', $emails)
            ->pluck('email');
    }

    public function create(DTO $data): User
    {
        return User::query()->create($data->toArray());
    }

    public function insert(array $data): bool
    {
        return User::query()->insert($data);
    }
}
