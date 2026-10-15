<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

session_start();

use Src\HttpFinish;
use Src\Route;

try {
    new Route();
} catch (HttpFinish $response) {
    http_response_code($response->status);
    foreach ($response->headers as $name => $value) {
        header("$name: $value");
    }
    echo $response->body;
}
