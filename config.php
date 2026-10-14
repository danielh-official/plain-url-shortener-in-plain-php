<?php

declare(strict_types=1);

return [
    // SQLite file is created on first request.
    'db_path' => __DIR__ . '/data/shortener.sqlite',

    // Length of the generated short codes.
    'code_length' => 6,
];
