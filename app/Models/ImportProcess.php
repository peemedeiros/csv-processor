<?php

namespace App\Models;

use App\Enum\ImportProcessStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'status',
        'processed_rows',
        'users_created',
        'failed_rows',
        'error_message',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'status' => ImportProcessStatus::class
        ];
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ImportProcessError::class);
    }
}
