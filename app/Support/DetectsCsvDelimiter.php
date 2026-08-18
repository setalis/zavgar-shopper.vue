<?php

declare(strict_types=1);

namespace App\Support;

use League\Csv\Reader;
use Throwable;

final class DetectsCsvDelimiter
{
    /**
     * Excel in UA/EU locales uses semicolon as the CSV list separator.
     */
    public const EXCEL = ';';

    /**
     * @var list<string>
     */
    public const CANDIDATES = [';', ',', '|', "\t"];

    /**
     * @var list<string>
     */
    private const DEFAULT_GUESSES = [
        'sku',
        'name',
        'slug',
        'summary',
        'description',
        'barcode',
        'type',
        'brand',
        'price',
        'quantity',
        'stock',
        'parent_sku',
        'attributes',
        'published_at',
        'is_visible',
        'featured',
    ];

    /**
     * @param  list<string>  $headerGuesses
     */
    public function handle(Reader $reader, array $headerGuesses = []): string
    {
        $currentHeaderOffset = $reader->getHeaderOffset();
        $currentDelimiter = $reader->getDelimiter();

        $best = self::EXCEL;
        $bestScore = -1;

        foreach (self::CANDIDATES as $delimiter) {
            $score = $this->score($this->headerCells($reader, $delimiter), $headerGuesses);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $delimiter;
            }
        }

        $reader->setHeaderOffset($currentHeaderOffset);
        $reader->setDelimiter($currentDelimiter);

        return $best;
    }

    /**
     * @param  list<string>  $headerGuesses
     */
    public function fromHeaderLine(string $headerLine, array $headerGuesses = []): ?string
    {
        $best = null;
        $bestScore = 1;

        foreach (self::CANDIDATES as $delimiter) {
            $cells = $this->splitCell($headerLine, $delimiter);
            $score = $this->score($cells, $headerGuesses);

            if (count($cells) > 1 && $score > $bestScore) {
                $bestScore = $score;
                $best = $delimiter;
            }
        }

        return $best;
    }

    /**
     * @param  list<mixed>  $values
     * @return list<string>
     */
    public function expandRow(array $values, string $delimiter): array
    {
        $values = array_values(array_map(strval(...), $values));

        if (count($values) !== 1) {
            return $values;
        }

        $parts = $this->splitCell($values[0], $delimiter);

        return count($parts) > 1 ? $parts : $values;
    }

    /**
     * @return list<string>
     */
    private function headerCells(Reader $reader, string $delimiter): array
    {
        $reader->setDelimiter($delimiter);
        $reader->setHeaderOffset(0);

        try {
            $header = $reader->getHeader();
        } catch (Throwable) {
            return [];
        }

        if (count($header) === 1) {
            $inner = $this->splitCell((string) $header[0], $delimiter);

            if (count($inner) > 1) {
                return $inner;
            }
        }

        return $header;
    }

    /**
     * @return list<string>
     */
    private function splitCell(string $cell, string $delimiter): array
    {
        if ($cell === '' || ! str_contains($cell, $delimiter)) {
            return [$cell];
        }

        $reader = Reader::fromString($cell);
        $reader->setDelimiter($delimiter);

        return array_map(strval(...), array_values($reader->first()));
    }

    /**
     * @param  list<string>  $header
     * @param  list<string>  $headerGuesses
     */
    private function score(array $header, array $headerGuesses): int
    {
        if ($header === []) {
            return 0;
        }

        $cells = array_map(
            fn (string $cell): string => mb_strtolower(trim($cell)),
            $header,
        );
        $guesses = array_map(
            fn (string $guess): string => mb_strtolower(trim($guess)),
            [...self::DEFAULT_GUESSES, ...$headerGuesses],
        );

        return (count(array_intersect($cells, $guesses)) * 1000) + count($header);
    }
}
