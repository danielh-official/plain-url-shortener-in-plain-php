<?php

declare(strict_types=1);

namespace Tests;

use Src\HttpFinish;

final class HelpersTest extends TestCase
{
    public function test_e_escapes_html_special_characters(): void
    {
        $this->assertSame(
            '&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;',
            e("<script>alert('x')</script>"),
        );
    }

    public function test_base_url_uses_http_host(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.test';
        unset($_SERVER['HTTPS']);

        $this->assertSame('http://example.test', baseUrl());
    }

    public function test_base_url_uses_https_when_enabled(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.test';
        $_SERVER['HTTPS'] = 'on';

        $this->assertSame('https://example.test', baseUrl());
    }

    public function test_csrf_token_is_stable_within_a_session(): void
    {
        $first = csrfToken();
        $second = csrfToken();

        $this->assertSame($first, $second);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $first);
    }

    public function test_redirect_throws_http_finish_with_location(): void
    {
        try {
            redirect('/next');
            $this->fail('Expected HttpFinish to be thrown.');
        } catch (HttpFinish $e) {
            $this->assertSame(302, $e->status);
            $this->assertSame('/next', $e->headers['Location']);
        }
    }
}
