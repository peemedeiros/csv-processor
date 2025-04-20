<?php

namespace Database\Factories;

use App\Models\ImportProcess;
use App\Models\ImportProcessError;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportProcessErrorFactory extends Factory
{
    protected $model = ImportProcessError::class;

    public function definition()
    {
        return [
            'import_process_id' => ImportProcess::factory(),
            'row_index' => $this->faker->numberBetween(1, 1000),
            'row_data' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'birth_date' => $this->faker->date('Y-m-d'),
                'phone' => $this->faker->phoneNumber(),
            ],
            'error_message' => $this->faker->sentence(),
            'error_type' => $this->faker->randomElement(['validation', 'database', 'format', 'unknown']),
        ];
    }

    /**
     * Estado para erros de validação
     */
    public function validationError(): self
    {
        return $this->state(function () {
            return [
                'error_type' => 'validation',
                'error_message' => $this->faker->randomElement([
                    'Email inválido',
                    'Campo nome obrigatório',
                    'Data de nascimento inválida',
                    'Formato de telefone inválido'
                ]),
            ];
        });
    }

    /**
     * Estado para erros de banco de dados
     */
    public function databaseError(): self
    {
        return $this->state(function () {
            return [
                'error_type' => 'database',
                'error_message' => $this->faker->randomElement([
                    'Email já cadastrado',
                    'Erro ao persistir no banco de dados',
                    'Violação de restrição de chave única',
                    'Erro de integridade referencial'
                ]),
            ];
        });
    }

    /**
     * Estado para erros de formato
     */
    public function formatError(): self
    {
        return $this->state(function () {
            return [
                'error_type' => 'format',
                'error_message' => $this->faker->randomElement([
                    'Formato CSV inválido',
                    'Colunas incompatíveis',
                    'Número incorreto de campos',
                    'Cabeçalho ausente'
                ]),
            ];
        });
    }
}
