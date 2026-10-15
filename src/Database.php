<?php

declare(strict_types=1);

namespace Src;

use PDO;

final class Database
{
    public static function connect(string $path): PDO
    {
        if ($path !== ':memory:') {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $pdo = new PDO("sqlite:$path", null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Idempotent: every statement uses IF NOT EXISTS.
        $pdo->exec((string) file_get_contents(__DIR__ . '/../schema.sql'));

        return $pdo;
    }
}
