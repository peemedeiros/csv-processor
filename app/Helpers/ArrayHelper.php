<?php

namespace App\Helpers;

class ArrayHelper
{
    public static function chunkFile(
        string $filePath,
        callable $generator,
        int $chunkSize = 1000
    ): \Generator
    {
        $file = fopen(storage_path("app/uploads/{$filePath}"), 'r');
        $data = [];

        for($i = 1; ($row = fgetcsv($file)) !== false; $i++) {

            $processedRow = $generator($row);

            if ($processedRow === null) {
                continue;
            }

            $data[] = $processedRow;

            if ($i % $chunkSize === 0) {
                yield $data;
                $data = [];
            }
        }

        if (!empty($data)) {
            yield $data;
        }

        fclose($file);
    }
}
