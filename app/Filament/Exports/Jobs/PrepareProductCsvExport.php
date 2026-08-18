<?php

declare(strict_types=1);

namespace App\Filament\Exports\Jobs;

use Filament\Actions\Exports\Jobs\PrepareCsvExport;

final class PrepareProductCsvExport extends PrepareCsvExport
{
    public function getExportCsvJob(): string
    {
        return ExportProductsCsv::class;
    }
}
