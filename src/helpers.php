<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function render(string $view, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require __DIR__ . '/../views/' . $view . '.php';
}

function baseUrl(): string
{
    $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';

    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

function csrfToken(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}

function redirect(string $to, int $status = 302): never
{
    header("Location: $to", true, $status);
    exit;
}
