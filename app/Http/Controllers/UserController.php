<?php

namespace App\Http\Controllers;

use App\DTOs\CreateUserDTO;
use App\Http\Requests\UserCreateRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Services\User\UserService;

class UserController extends Controller
{
    public function __construct(protected UserService $userService)
    {
    }

    public function index(): UserCollection
    {
        $users = $this->userService->getPaginatedUsers(20);

        return new UserCollection($users);
    }

    public function store(UserCreateRequest $request): UserResource
    {
        $data = CreateUserDTO::fromArray($request->validated());

        $user = $this->userService->createUser($data);

        return new UserResource($user);
    }
}
