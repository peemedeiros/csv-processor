<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportProcessError extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_process_id',
        'row_index',
        'row_data',
        'error_message',
        'error_type',
    ];

    protected $casts = [
        'row_data' => 'array',
    ];

    public function importProcess(): BelongsTo
    {
        return $this->belongsTo(ImportProcess::class);
    }

}
