<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileMaxSize implements ValidationRule
{
    protected int $maxSize;
    public function __construct()
    {
        $maxSizeInMB = config('app.file_max_size');

        $this->maxSize = $maxSizeInMB * 1024 * 1024;
    }


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value->getSize() > $this->maxSize) {
            $maxSizeMB = $this->maxSize / (1024 * 1024);
            $fail("O arquivo :attribute não deve exceder {$maxSizeMB} MB.");
        }
    }
}
