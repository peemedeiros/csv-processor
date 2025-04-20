<?php

namespace App\DTOs;

use App\Enum\ImportProcessStatus;

class CreateImportProcessDTO implements DTO
{
    public function __construct(
        private string $file_name,
        private ImportProcessStatus $status,
        private int $processed_rows = 0,
        private int $failed_rows = 0,
        private ?string $error_message = null,
        private ?string $completed_at = null,
    )
    {

    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
