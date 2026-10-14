<?php

declare(strict_types=1);

namespace Src;

use function render;
use function baseUrl;
use function csrfToken;
use function redirect;

final class Route
{
    public function __construct()
    {
        $config = require __DIR__ . '/../config.php';

        $db        = Database::connect($config['db_path']);
        $links     = new LinkRepository($db);
        $shortener = new Shortener($links, $config['code_length']);

        $method = $_SERVER['REQUEST_METHOD'];
        $path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

        // GET / : form, flash message, recent links
        if ($method === 'GET' && $path === '/') {
            $created = null;
            if (isset($_GET['code']) && is_string($_GET['code'])) {
                $created = $links->find($_GET['code']);
            }

            render('home', [
                'error' => $_GET['error'] ?? null,
                'created' => $created,
                'recent' => $links->recent(),
                'baseUrl' => baseUrl(),
                'csrf' => csrfToken(),
            ]);
            exit;
        }

        // POST /shorten : validate, store, redirect back (post/redirect/get)
        if ($method === 'POST' && $path === '/shorten') {
            $token = $_POST['csrf'] ?? '';
            if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
                http_response_code(400);
                exit('Invalid CSRF token.');
            }

            $url = trim((string) ($_POST['url'] ?? ''));
            if (!$shortener->isValidUrl($url)) {
                redirect('/?error=' . urlencode('Please enter a valid http or https URL.'));
            }

            redirect('/?code=' . $shortener->shorten($url));
        }

        // GET /{code}/stats : tiny JSON endpoint, handy for testing
        if ($method === 'GET' && preg_match('#^/([0-9A-Za-z]{4,16})/stats$#', $path, $m)) {
            $link = $links->find($m[1]);
            if ($link === null) {
                http_response_code(404);
                exit('Not found.');
            }

            header('Content-Type: application/json');
            echo json_encode([
                'code' => $link['code'],
                'url' => $link['url'],
                'clicks' => (int) $link['clicks'],
                'created_at' => $link['created_at'],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }

        // GET /{code} : count the click and redirect (302 so every visit hits the server)
        if ($method === 'GET' && preg_match('#^/([0-9A-Za-z]{4,16})$#', $path, $m)) {
            $link = $links->find($m[1]);
            if ($link === null) {
                http_response_code(404);
                exit('Not found.');
            }

            $links->recordClick($link['code']);
            redirect($link['url']);
        }

        http_response_code(404);
        echo 'Not found.';

    }
}