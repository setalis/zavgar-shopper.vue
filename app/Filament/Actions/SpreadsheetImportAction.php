<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Support\ConvertsSpreadsheetToCsv;
use App\Support\DetectsCsvDelimiter;
use App\Support\NormalizesImportCsv;
use Closure;
use Filament\Actions\ImportAction;
use Filament\Actions\Imports\ImportColumn;
use Filament\Forms\Components\FileUpload;
use League\Csv\Reader as CsvReader;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class SpreadsheetImportAction extends ImportAction
{
    /**
     * @var list<string>
     */
    private const SPREADSHEET_MIME_TYPES = [
        'text/csv',
        'text/x-csv',
        'application/csv',
        'application/x-csv',
        'text/comma-separated-values',
        'text/x-comma-separated-values',
        'text/plain',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $parentSchema = $this->schema;

        $this->schema(function (ImportAction $action) use ($parentSchema): array {
            $components = $parentSchema instanceof Closure
                ? $parentSchema($action)
                : ($parentSchema ?? []);

            foreach ($components as $component) {
                if ($component instanceof FileUpload && $component->getName() === 'file') {
                    $component
                        ->acceptedFileTypes(self::SPREADSHEET_MIME_TYPES)
                        ->placeholder(__('backend.product_imports.import_file_placeholder'));
                }
            }

            return $components;
        });
    }

    /**
     * @return array<mixed>
     */
    public function getFileValidationRules(): array
    {
        return array_map(
            fn (mixed $rule): mixed => $rule === 'extensions:csv,txt' ? 'extensions:csv,txt,xlsx' : $rule,
            parent::getFileValidationRules(),
        );
    }

    /**
     * @return resource|false
     */
    public function getUploadedFileStream(TemporaryUploadedFile $file)
    {
        $converter = app(ConvertsSpreadsheetToCsv::class);
        $path = $this->resolveUploadedPath($file);

        if ($path !== null && (
            $converter->supports($file->getClientOriginalName() ?: $file->getFilename(), $file->getMimeType())
            || $converter->isSpreadsheet($path)
        )) {
            return $this->normalizeStream($converter->handle($path));
        }

        $stream = parent::getUploadedFileStream($file);

        if ($stream === false) {
            return false;
        }

        return $this->normalizeStream($stream);
    }

    protected function guessCsvDelimiter(?CsvReader $reader = null): ?string
    {
        if (! $reader) {
            return DetectsCsvDelimiter::EXCEL;
        }

        return app(DetectsCsvDelimiter::class)->handle($reader, $this->headerGuesses());
    }

    /**
     * @param  resource  $stream
     * @return resource
     */
    private function normalizeStream($stream)
    {
        return app(NormalizesImportCsv::class)->handle($stream, $this->headerGuesses());
    }

    private function resolveUploadedPath(TemporaryUploadedFile $file): ?string
    {
        $path = $file->getRealPath();

        if (is_string($path) && is_readable($path)) {
            return $path;
        }

        $path = tempnam(sys_get_temp_dir(), 'import');

        if ($path === false) {
            return null;
        }

        file_put_contents($path, $file->get());

        return $path;
    }

    /**
     * @return list<string>
     */
    private function headerGuesses(): array
    {
        $importer = $this->getImporter();

        if (! is_string($importer) || ! class_exists($importer)) {
            return [];
        }

        $guesses = [];

        foreach ($importer::getColumns() as $column) {
            if (! $column instanceof ImportColumn) {
                continue;
            }

            foreach ($column->getGuesses() as $guess) {
                $guesses[] = $guess;
            }
        }

        return $guesses;
    }
}
