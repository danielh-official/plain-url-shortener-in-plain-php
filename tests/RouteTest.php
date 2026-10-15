<?php

declare(strict_types=1);

namespace Tests;

use Src\HttpFinish;

final class RouteTest extends TestCase
{
    public function test_home_renders_form(): void
    {
        $html = $this->dispatch('GET', '/');

        $this->assertStringContainsString('<h1>Shortener</h1>', $html);
        $this->assertStringContainsString('action="/shorten"', $html);
    }

    public function test_shorten_rejects_invalid_csrf(): void
    {
        $_POST = ['csrf' => 'bad', 'url' => 'https://example.com'];

        try {
            $this->dispatch('POST', '/shorten');
            $this->fail('Expected HttpFinish to be thrown.');
        } catch (HttpFinish $e) {
            $this->assertSame(400, $e->status);
            $this->assertSame('Invalid CSRF token.', $e->body);
        }
    }

    public function test_shorten_rejects_invalid_url(): void
    {
        $_POST = ['csrf' => csrfToken(), 'url' => 'not-a-url'];

        try {
            $this->dispatch('POST', '/shorten');
            $this->fail('Expected HttpFinish to be thrown.');
        } catch (HttpFinish $e) {
            $this->assertSame(302, $e->status);
            $this->assertStringContainsString('error=', $e->headers['Location']);
        }
    }

    public function test_shorten_stores_url_and_redirects_with_code(): void
    {
        $_POST = ['csrf' => csrfToken(), 'url' => 'https://example.com/page'];

        try {
            $this->dispatch('POST', '/shorten');
            $this->fail('Expected HttpFinish to be thrown.');
        } catch (HttpFinish $e) {
            $this->assertSame(302, $e->status);
            $this->assertMatchesRegularExpression('#^/\?code=[0-9A-Za-z]{6}$#', $e->headers['Location']);

            parse_str(parse_url($e->headers['Location'], PHP_URL_QUERY), $query);
            $this->assertSame('https://example.com/page', $this->links->find($query['code'])['url']);
        }
    }

    public function test_follow_increments_clicks_and_redirects(): void
    {
        $this->links->create('abc123', 'https://example.com/dest');

        try {
            $this->dispatch('GET', '/abc123');
            $this->fail('Expected HttpFinish to be thrown.');
        } catch (HttpFinish $e) {
            $this->assertSame(302, $e->status);
            $this->assertSame('https://example.com/dest', $e->headers['Location']);
            $this->assertSame(1, (int) $this->links->find('abc123')['clicks']);
        }
    }

    public function test_stats_returns_json_for_existing_link(): void
    {
        $this->links->create('abc123', 'https://example.com');

        $body = $this->dispatch('GET', '/abc123/stats');
        $data = json_decode($body, true);

        $this->assertSame('abc123', $data['code']);
        $this->assertSame('https://example.com', $data['url']);
        $this->assertSame(0, $data['clicks']);
    }

    public function test_stats_returns_404_for_missing_link(): void
    {
        try {
            $this->dispatch('GET', '/missing/stats');
            $this->fail('Expected HttpFinish to be thrown.');
        } catch (HttpFinish $e) {
            $this->assertSame(404, $e->status);
        }
    }

    public function test_delete_removes_link_and_redirects_home(): void
    {
        $this->links->create('abc123', 'https://example.com');
        $_POST = ['csrf' => csrfToken()];

        try {
            $this->dispatch('POST', '/abc123/delete');
            $this->fail('Expected HttpFinish to be thrown.');
        } catch (HttpFinish $e) {
            $this->assertSame(302, $e->status);
            $this->assertSame('/', $e->headers['Location']);
            $this->assertNull($this->links->find('abc123'));
        }
    }

    public function test_unknown_path_returns_404(): void
    {
        try {
            $this->dispatch('GET', '/nope/too/deep');
            $this->fail('Expected HttpFinish to be thrown.');
        } catch (HttpFinish $e) {
            $this->assertSame(404, $e->status);
            $this->assertSame('Not found.', $e->body);
        }
    }
}
