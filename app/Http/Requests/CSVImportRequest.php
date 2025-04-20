<?php

namespace App\Http\Requests;

use App\Rules\FileMaxSize;
use Illuminate\Foundation\Http\FormRequest;

class CSVImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv',
                'min:1',
                new FileMaxSize(),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Por favor, selecione um arquivo para importar.',
            'file.file' => 'O upload deve ser um arquivo válido.',
            'file.mimes' => 'O arquivo deve ser do tipo CSV.',
            'file.max' => 'O arquivo não pode ser maior que 10MB.',
        ];
    }

}
