<?php

declare(strict_types=1);

use App\Support\ConvertsSpreadsheetToCsv;
use App\Support\DetectsCsvDelimiter;
use App\Support\NormalizesImportCsv;
use League\Csv\Info;
use League\Csv\Reader;
use League\Csv\Statement;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

test('semicolon wins even when product names contain many commas', function (): void {
    $csv = Reader::fromString("sku;name;summary\nSKU-1;Shirt, cotton, blue, large;Nice, soft, and, warm\n");

    $stats = Info::getDelimiterStats($csv, delimiters: [',', ';', '|', "\t"], limit: 10);
    $filamentGuess = array_search(max($stats), $stats, true);

    expect($filamentGuess)->toBe(',')
        ->and((new DetectsCsvDelimiter)->handle($csv))->toBe(';');
});

test('comma-separated headers are still detected', function (): void {
    $csv = Reader::fromString("sku,name,summary\nSKU-1,Shirt,Nice\n");

    expect((new DetectsCsvDelimiter)->handle($csv))->toBe(',');
});

test('quoted single-column excel csv is expanded into separate columns', function (): void {
    $stream = fopen('php://temp', 'r+');
    fwrite($stream, "\"sku;name;price\"\n\"SKU-1;Shirt, cotton;1800\"\n");
    rewind($stream);

    $normalized = (new NormalizesImportCsv(new DetectsCsvDelimiter))->handle($stream);
    $csv = Reader::from($normalized);
    $csv->setDelimiter(';');
    $csv->setHeaderOffset(0);

    expect($csv->getHeader())->toBe(['sku', 'name', 'price'])
        ->and((new Statement)->process($csv)->first())->toMatchArray([
            'sku' => 'SKU-1',
            'name' => 'Shirt, cotton',
            'price' => '1800',
        ]);
});

test('excel workbook with all columns in one cell is split on import', function (): void {
    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('products-one-cell-', true).'.xlsx';

    $writer = new Writer;
    $writer->openToFile($path);
    $writer->addRow(Row::fromValues(['sku;name;price']));
    $writer->addRow(Row::fromValues(['SKU-ONE-CELL;Excel name;1800']));
    $writer->close();

    $stream = (new ConvertsSpreadsheetToCsv(new DetectsCsvDelimiter))->handle($path);
    $csv = Reader::from($stream);
    $csv->setHeaderOffset(0);

    expect($csv->getHeader())->toBe(['sku', 'name', 'price'])
        ->and((new Statement)->process($csv)->first())->toMatchArray([
            'sku' => 'SKU-ONE-CELL',
            'name' => 'Excel name',
            'price' => '1800',
        ]);

    unlink($path);
});
