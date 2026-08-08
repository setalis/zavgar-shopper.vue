<?php

declare(strict_types=1);

$names = json_decode(
    file_get_contents(__DIR__.'/../vendor/shopper/core/resources/lang/countries/en.json'),
    true,
);

$out = [];

foreach ($names as $code => $name) {
    $out[$code] = Locale::getDisplayRegion('_'.$code, 'uk') ?: $name;
}

file_put_contents(
    __DIR__.'/../lang/countries/uk.json',
    json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL,
);

echo 'Generated '.count($out)." country names\n";
