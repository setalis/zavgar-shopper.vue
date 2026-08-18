<?php

declare(strict_types=1);

namespace App\Filament\Exports\Jobs;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use App\Filament\Exports\ProductExporter;
use App\Models\Product;
use Filament\Actions\Exports\Jobs\ExportCsv;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use SplTempFileObject;
use Throwable;

final class ExportProductsCsv extends ExportCsv
{
    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $user = $this->export->user;

        auth()->setUser($user);

        try {
            $processedRows = 0;
            $successfulRows = 0;

            $csv = Writer::from(new SplTempFileObject);
            $csv->setDelimiter($this->exporter::getCsvDelimiter());

            $query = EloquentSerializeFacade::unserialize($this->query);

            foreach ($this->exporter->getCachedColumns() as $column) {
                $column->applyRelationshipAggregates($query);
                $column->applyEagerLoading($query);
            }

            foreach ($query->find($this->records) as $record) {
                try {
                    foreach ($this->rowsFor($record) as $row) {
                        $csv->insertOne($row);
                        $successfulRows++;
                    }
                } catch (Throwable $exception) {
                    report($exception);
                }

                $processedRows++;
            }

            $filePath = $this->export->getFileDirectory().DIRECTORY_SEPARATOR.str_pad(strval($this->page), 16, '0', STR_PAD_LEFT).'.csv';

            DB::transaction(function () use ($csv, $filePath, $processedRows, $successfulRows): void {
                $this->export::query()
                    ->where('id', $this->export->getKey())
                    ->lockForUpdate()
                    ->update([
                        'processed_rows' => new Expression('processed_rows + '.$processedRows),
                        'successful_rows' => new Expression('successful_rows + '.$successfulRows),
                    ]);

                $this->export::query()
                    ->where('id', $this->export->getKey())
                    ->whereColumn('processed_rows', '>', 'total_rows')
                    ->lockForUpdate()
                    ->update([
                        'processed_rows' => new Expression('total_rows'),
                    ]);

                $this->export->getFileDisk()->put($filePath, $csv->toString(), Filesystem::VISIBILITY_PRIVATE);
            });
        } finally {
            auth()->forgetGuards();
        }
    }

    /**
     * @return list<list<mixed>>
     */
    private function rowsFor(mixed $record): array
    {
        if ($this->exporter instanceof ProductExporter && $record instanceof Product) {
            return $this->exporter->rowsFor($record);
        }

        return [($this->exporter)($record)];
    }
}
