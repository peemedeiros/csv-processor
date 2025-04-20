<?php

namespace Database\Factories;

use App\Enum\ImportProcessStatus;
use App\Models\ImportProcess;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportProcessFactory extends Factory
{
    protected $model = ImportProcess::class;

    public function definition()
    {
        return [
            'file_name' => $this->faker->word() . '.csv',
            'status' => $this->faker->randomElement(ImportProcessStatus::cases()),
            'processed_rows' => $this->faker->numberBetween(0, 1000),
            'users_created' => function (array $attributes) {
                return $this->faker->numberBetween(0, $attributes['processed_rows']);
            },
            'failed_rows' => function (array $attributes) {
                return $attributes['processed_rows'] - $attributes['users_created'];
            },
            'error_message' => $this->faker->optional(0.3)->sentence(),
            'completed_at' => $this->faker->optional(0.7)->dateTimeThisMonth(),
        ];
    }

    /**
     * Indica que o processo está pendente.
     */
    public function pending(): self
    {
        return $this->state(function () {
            return [
                'status' => ImportProcessStatus::PENDING,
                'processed_rows' => 0,
                'users_created' => 0,
                'failed_rows' => 0,
                'error_message' => null,
                'completed_at' => null,
            ];
        });
    }

    /**
     * Indica que o processo está em andamento.
     */
    public function processing(): self
    {
        return $this->state(function () {
            return [
                'status' => ImportProcessStatus::PROCESSING,
                'completed_at' => null,
            ];
        });
    }

    /**
     * Indica que o processo foi concluído com sucesso.
     */
    public function completed(): self
    {
        return $this->state(function () {
            return [
                'status' => ImportProcessStatus::DONE,
                'completed_at' => now(),
                'error_message' => null,
            ];
        });
    }

    /**
     * Indica que o processo falhou.
     */
    public function failed(): self
    {
        return $this->state(function () {
            return [
                'status' => ImportProcessStatus::FAILED,
                'completed_at' => now(),
                'error_message' => $this->faker->sentence(),
            ];
        });
    }
}
