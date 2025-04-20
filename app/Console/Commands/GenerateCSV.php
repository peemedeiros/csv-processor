<?php

namespace App\Console\Commands;

use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as CommandAlias;

class GenerateCSV extends Command
{
    protected $signature = 'app:generate-csv {rows=100}';

    protected $description = 'Command description';

    public function handle()
    {
        $linhas = (int) $this->argument('rows');

        if ($linhas <= 0) {
            $this->error('O número de linhas deve ser maior que zero.');
            return CommandAlias::FAILURE;
        }

        $faker = Factory::create('pt_BR');

        $csvFileName = 'test.csv';

        if (!Storage::exists('uploads')) {
            Storage::makeDirectory('uploads');
        }

        $filePath = 'uploads/' . $csvFileName;

        $file = fopen(storage_path('app/' . $filePath), 'w');

        fputcsv($file, ['nome', 'email', 'data_nascimento']);

        $this->output->progressStart($linhas);

        for ($i = 0; $i < $linhas; $i++) {
            $nome = $faker->name;
            $email = $faker->unique()->email;
            $dataNascimento = $faker->date('d/m/Y', '-18 years');

            fputcsv($file, [$nome, $email, $dataNascimento]);

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        fclose($file);

        $this->info("Arquivo CSV gerado com sucesso em storage/app/{$filePath}");

        return CommandAlias::SUCCESS;

    }
}
