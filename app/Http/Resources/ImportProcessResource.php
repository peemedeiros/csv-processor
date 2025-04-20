<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportProcessResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'status' => $this->status->name,
            'processed_rows' => $this->processed_rows,
            'users_created' => $this->users_created,
            'failed_rows' => $this->failed_rows,
            'error_message' => $this->error_message,
            'completed_at' => (!empty($this->completed_at)) ?
                Carbon::parse($this->completed_at)->format('d/m/Y H:i:s') : null,
            'created_at' => Carbon::parse($this->created_at)->format('d/m/Y H:i:s'),
        ];
    }
}
