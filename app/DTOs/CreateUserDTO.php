<?php

namespace App\DTOs;

use Illuminate\Support\Facades\Hash;

class CreateUserDTO implements DTO
{
    public function __construct(
        private string $name,
        private string $email,
        private string $birth_date,
        private string $password,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'birth_date' => $this->birth_date,
            'password' => Hash::make($this->password),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['email'],
            $data['birth_date'],
            $data['password'],
        );
    }
}
