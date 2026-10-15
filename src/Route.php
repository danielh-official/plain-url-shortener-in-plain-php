<?php

declare(strict_types=1);

namespace Src;

use function render;
use function baseUrl;
use function csrfToken;
use function redirect;

final class Route
{
    private LinkRepository $links;
    private Shortener $shortener;

    public function __construct()
    {
        $config = require __DIR__ . '/../config.php';

        $db = Database::connect($config['db_path']);
        $this->links = new LinkRepository($db);
        $this->shortener = new Shortener($this->links, $config['code_length']);

        $method = $_SERVER['REQUEST_METHOD'];
        $path = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

        $this->handle($method, $path);
    }

    private function handle(string $method, string $path): void
    {
        if ($method === 'GET' && $path === '/') {
            $this->home();
            return;
        }

        if ($method === 'POST' && $path === '/shorten') {
            $this->shorten();
            return;
        }

        if ($method === 'POST' && preg_match('#^/([0-9A-Za-z]{4,16})/delete$#', $path, $m)) {
            $this->delete($m[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/([0-9A-Za-z]{4,16})/stats$#', $path, $m)) {
            $this->stats($m[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/([0-9A-Za-z]{4,16})$#', $path, $m)) {
            $this->follow($m[1]);
            return;
        }

        $this->notFound();
    }

    private function home(): void
    {
        $created = null;
        if (isset($_GET['code']) && is_string($_GET['code'])) {
            $created = $this->links->find($_GET['code']);
        }

        render('home', [
            'error'   => $_GET['error'] ?? null,
            'created' => $created,
            'recent'  => $this->links->recent(),
            'baseUrl' => baseUrl(),
            'csrf'    => csrfToken(),
        ]);
    }

    private function shorten(): void
    {
        $this->requireCsrf();

        $url = trim((string) ($_POST['url'] ?? ''));
        if (!$this->shortener->isValidUrl($url)) {
            redirect('/?error=' . urlencode('Please enter a valid http or https URL.'));
        }

        redirect('/?code=' . $this->shortener->shorten($url));
    }

    private function stats(string $code): void
    {
        $link = $this->links->find($code);
        if ($link === null) {
            $this->notFound();
        }

        header('Content-Type: application/json');
        echo json_encode([
            'code'       => $link['code'],
            'url'        => $link['url'],
            'clicks'     => (int) $link['clicks'],
            'created_at' => $link['created_at'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function follow(string $code): void
    {
        $link = $this->links->find($code);
        if ($link === null) {
            $this->notFound();
        }

        $this->links->recordClick($link['code']);
        redirect($link['url']);
    }

    private function requireCsrf(): void
    {
        $token = $_POST['csrf'] ?? '';
        if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
            http_response_code(400);
            exit('Invalid CSRF token.');
        }
    }

    private function notFound(): never
    {
        http_response_code(404);
        exit('Not found.');
    }

    private function delete(string $code): void
    {
        $this->requireCsrf();

        if ($this->links->find($code) === null) {
            $this->notFound();
        }

        $this->links->delete($code);
        redirect('/');
    }
}
