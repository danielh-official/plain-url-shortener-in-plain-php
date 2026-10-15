<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Src\Database;
use Src\HttpFinish;
use Src\LinkRepository;
use Src\Route;
use Src\Shortener;

abstract class TestCase extends BaseTestCase
{
    protected LinkRepository $links;
    protected Shortener $shortener;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_HOST'] = 'localhost';
        unset($_SERVER['HTTPS']);

        $db = Database::connect(':memory:');
        $this->links = new LinkRepository($db);
        $this->shortener = new Shortener($this->links, 6);
    }

    /** Run a request against Route with the in-memory repository. */
    protected function dispatch(string $method, string $path): string
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $path;

        ob_start();
        try {
            new Route($this->links, $this->shortener);

            return (string) ob_get_clean();
        } catch (HttpFinish $e) {
            ob_end_clean();
            throw $e;
        }
    }
}
