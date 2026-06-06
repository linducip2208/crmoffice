<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class CsvImportService
{
    protected array $errors = [];

    /**
     * Parse a CSV file and return header + rows.
     *
     * @return array{header: array, rows: array}
     */
    public function parse(string $filePath): array
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($lines)) {
            return ['header' => [], 'rows' => []];
        }

        $header = array_map(fn ($h) => trim(strtolower($h)), str_getcsv(array_shift($lines), escape: "\\"));

        $rows = [];
        foreach ($lines as $line) {
            $row = str_getcsv($line, escape: "\\");
            if (count($row) !== count($header)) {
                continue;
            }
            $data = @array_combine($header, $row);
            if ($data) {
                $rows[] = $data;
            }
        }

        return ['header' => $header, 'rows' => $rows];
    }

    /**
     * Validate rows against rules.
     *
     * @return array{array, array} [validRows, errors]
     */
    public function validateRows(array $rows, array $rules, array $messages = []): array
    {
        $validRows = [];
        $this->errors = [];

        foreach ($rows as $i => $row) {
            $validator = Validator::make($row, $rules, $messages);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->errors[] = "Row " . ($i + 2) . ": {$error}";
                }
            } else {
                $validRows[] = $row;
            }
        }

        return [$validRows, $this->errors];
    }

    /**
     * Bulk insert validated rows into a model.
     *
     * @param  string  $modelClass
     * @param  array  $rows
     * @param  array  $columnMap  [csvColumn => dbColumn]
     * @param  array  $defaults   Default values for all rows
     * @param  callable|null  $beforeCreate  Hook before each insert
     * @return array{count: int, errors: array}
     */
    public function import(
        string $modelClass,
        array $rows,
        array $columnMap,
        array $defaults = [],
        ?callable $beforeCreate = null,
    ): array {
        $count = 0;
        $this->errors = [];
        $batch = [];
        $batchSize = 100;

        foreach ($rows as $i => $row) {
            $data = $defaults;

            foreach ($columnMap as $csvCol => $dbCol) {
                if ($dbCol instanceof \Closure) {
                    $result = $dbCol($row);
                    if (is_array($result)) {
                        $data = array_merge($data, $result);
                    }
                } elseif (array_key_exists($csvCol, $row)) {
                    $data[$dbCol] = $row[$csvCol];
                }
            }

            if ($beforeCreate) {
                try {
                    $data = $beforeCreate($data, $row, $i);
                    if ($data === false) {
                        $this->errors[] = "Row " . ($i + 2) . ": skipped by validation hook";
                        continue;
                    }
                } catch (\Throwable $e) {
                    $this->errors[] = "Row " . ($i + 2) . ": {$e->getMessage()}";
                    continue;
                }
            }

            $batch[] = $data;
            $count++;

            if (count($batch) >= $batchSize) {
                $modelClass::insert($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            $modelClass::insert($batch);
        }

        return ['count' => $count, 'errors' => $this->errors];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
