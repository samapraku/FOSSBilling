<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/src/vendor',
        __DIR__ . '/src/data',
        UnionTypesRector::class,
        MixedTypeRector::class,
    ]);

    $rectorConfig->sets([
        SetList::PHP_80,
    ]);
};
