<?php

namespace App\Actions\CSV;

use Carbon\Carbon;

class ProcessCSVRow
{
    private string $defaultPassword;
    private array $errors = [];
    private int $validCount = 0;
    private int $invalidCount = 0;
    private int $errorBufferSize = 100;
    private \Closure $errorHandler;


    public function __invoke(array $row): ?array
    {
        try {
            if ($this->checkHeaderRow($row)) {
                return null;
            }

            [$name, $email, $birthDate] = $this->validateRow($row);

            $this->validCount++;

            return [
                'name' => $name,
                'email' => $email,
                'birth_date' => $birthDate,
                'password' => $this->defaultPassword,
            ];
        } catch (\Exception $e) {
            $this->invalidCount++;

            $this->errors[] = [
                'row_data' => $row,
                'error_message' => $e->getMessage(),
                'error_type' => get_class($e),
                'timestamp' => now()->toDateTimeString(),
            ];

            if (count($this->errors) >= $this->errorBufferSize) {
                call_user_func($this->errorHandler, $this->errors);
                $this->errors = [];
            }

            return null;
        }
    }

    public function setErrorHandler(callable $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }

    public function setDefaultPassword(string $hashedPassword): void
    {
        $this->defaultPassword = $hashedPassword;
    }

    public function flushErrors(): array
    {
        $pendingErrors = $this->errors;
        $this->errors = [];
        return $pendingErrors;
    }

    public function getValidCount(): int
    {
        return $this->validCount;
    }

    public function getInvalidCount(): int
    {
        return $this->invalidCount;
    }

    private function validateRow(array $row): array
    {
        if (!isset($row[0], $row[1], $row[2])) {
            throw new \InvalidArgumentException(
                "Linha incompleta. Necessário nome, email e data de nascimento."
            );
        }

        $name = trim($row[0]);
        $email = trim($row[1]);
        $birthDate = trim($row[2]);

        if (empty($name)) {
            throw new \InvalidArgumentException("Nome não pode estar vazio.");
        }

        if (strlen($name) < 3) {
            throw new \InvalidArgumentException("Nome precisa ter pelo menos 3 caracteres.");
        }

        if (empty($email)) {
            throw new \InvalidArgumentException("Email não pode estar vazio.");
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException("O email é inválido.");
        }

        if (empty($birthDate)) {
            throw new \InvalidArgumentException("Data de nascimento não pode estar vazia.");
        }

        $date = Carbon::createFromFormat('d/m/Y', $birthDate);

        if ($date->isFuture()) {
            throw new \InvalidArgumentException('A data de nascimento não pode ser no futuro.');
        }

        if ($date->age > 120) {
            throw new \InvalidArgumentException('A idade máxima permitida é 120 anos.');
        }

        if ($date->format('d/m/Y') !== $birthDate) {
            throw new \InvalidArgumentException('Data inválida.');
        }

        $birthDate = $date->format('Y-m-d');

        return [$name, $email, $birthDate];
    }

    private function checkHeaderRow(array $row): bool
    {
        if ($row[0] != 'nome' && $row[1] != 'email' && $row[2] != 'data_nascimento') {
            return false;
        }

        return true;
    }
}
