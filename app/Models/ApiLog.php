<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $table = 'api_logs';

    protected $fillable = [
        'method',
        'url',
        'ip_address',
        'user_agent',
        'user_id',
        'request_data',
        'response_data',
        'status_code',
        'execution_time',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'execution_time' => 'float',
    ];

    // Relação com o usuário, se aplicável
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
