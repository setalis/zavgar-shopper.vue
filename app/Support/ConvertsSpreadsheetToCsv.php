<?php

declare(strict_types=1);

namespace App\Support;

use DateInterval;
use DateTimeInterface;
use League\Csv\Writer;
use OpenSpout\Reader\XLSX\Reader;
use RuntimeException;
use ZipArchive;

final class ConvertsSpreadsheetToCsv
{
    private ?string $embeddedDelimiter = null;

    public function __construct(private DetectsCsvDelimiter $delimiterDetector) {}

    public function supports(string $filename, ?string $mime = null): bool
    {
        if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'xlsx') {
            return true;
        }

        return $mime === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }

    public function isSpreadsheet(string $path): bool
    {
        if (! is_readable($path) || ! class_exists(ZipArchive::class)) {
            return false;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        $magic = fread($handle, 4);
        fclose($handle);

        if ($magic !== "PK\x03\x04") {
            return false;
        }

        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return false;
        }

        $hasWorkbook = $zip->locateName('xl/workbook.xml') !== false;
        $zip->close();

        return $hasWorkbook;
    }

    /**
     * @return resource
     */
    public function handle(string $path)
    {
        $reader = new Reader;
        $reader->open($path);

        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            $reader->close();

            throw new RuntimeException('Unable to create a temporary CSV stream.');
        }

        $writer = Writer::from($stream);

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($row->isEmpty()) {
                        continue;
                    }

                    $writer->insertOne($this->cellsForCsv($row->toArray()));
                }

                break;
            }
        } finally {
            $reader->close();
        }

        rewind($stream);

        return $stream;
    }

    /**
     * @param  list<null|bool|DateInterval|DateTimeInterface|float|int|string>  $values
     * @return list<string>
     */
    private function cellsForCsv(array $values): array
    {
        $values = $this->stringifyRow($values);

        if (count($values) !== 1) {
            return $values;
        }

        if ($this->embeddedDelimiter === null) {
            $this->embeddedDelimiter = $this->delimiterDetector->fromHeaderLine($values[0]) ?? '';
        }

        if ($this->embeddedDelimiter === '') {
            return $values;
        }

        return $this->delimiterDetector->expandRow($values, $this->embeddedDelimiter);
    }

    /**
     * @param  list<null|bool|DateInterval|DateTimeInterface|float|int|string>  $values
     * @return list<string>
     */
    private function stringifyRow(array $values): array
    {
        return array_map($this->stringify(...), $values);
    }

    private function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof DateInterval) {
            return $value->format('%H:%I:%S');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return (string) $value;
    }
}
