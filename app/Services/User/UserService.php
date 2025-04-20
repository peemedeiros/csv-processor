<?php

namespace App\Services\User;

use App\DTOs\CreateUserDTO;
use App\Models\User;
use App\Repositories\UserRepository\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(protected UserRepositoryInterface $userRepository)
    {
    }

    public function getPaginatedUsers(int $perPage): LengthAwarePaginator
    {
        return $this->userRepository->getAll($perPage);
    }

    public function createUser(CreateUserDTO $userDTO): User
    {
        return $this->userRepository->create($userDTO);
    }
}
