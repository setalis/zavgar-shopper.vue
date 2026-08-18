<?php

declare(strict_types=1);

namespace App\Support;

use League\Csv\Reader;
use League\Csv\Writer;
use RuntimeException;

final class NormalizesImportCsv
{
    public function __construct(private DetectsCsvDelimiter $detector) {}

    /**
     * @param  resource  $stream
     * @param  list<string>  $headerGuesses
     * @return resource
     */
    public function handle($stream, array $headerGuesses = [])
    {
        rewind($stream);

        $reader = Reader::from($stream);
        $delimiter = $this->detector->handle($reader, $headerGuesses);

        rewind($stream);

        $reader = Reader::from($stream);
        $reader->setDelimiter($delimiter);

        $output = fopen('php://temp', 'r+');

        if ($output === false) {
            throw new RuntimeException('Unable to create a temporary CSV stream.');
        }

        $writer = Writer::from($output);
        $writer->setDelimiter($delimiter);

        foreach ($reader->getRecords() as $record) {
            $writer->insertOne($this->detector->expandRow(array_values($record), $delimiter));
        }

        rewind($output);

        return $output;
    }
}
