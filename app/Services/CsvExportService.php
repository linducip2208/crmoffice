<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /**
     * @param  Builder  $query
     * @param  array<string, string|\Closure>  $columns  [headerLabel => 'db.column' or Closure]
     * @param  string  $filename
     * @return StreamedResponse
     */
    public function download(Builder $query, array $columns, string $filename = 'export.csv'): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM for Excel UTF-8

            fputcsv($handle, array_keys($columns));

            $query->chunk(500, function ($records) use ($handle, $columns) {
                foreach ($records as $record) {
                    $row = [];
                    foreach ($columns as $accessor) {
                        if ($accessor instanceof \Closure) {
                            $row[] = $accessor($record);
                        } else {
                            $row[] = data_get($record, $accessor, '');
                        }
                    }
                    fputcsv($handle, $row);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
