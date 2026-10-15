<?php

declare(strict_types=1);

namespace Tests;

final class ShortenerTest extends TestCase
{
    public function test_accepts_http_and_https_urls(): void
    {
        $this->assertTrue($this->shortener->isValidUrl('http://example.com'));
        $this->assertTrue($this->shortener->isValidUrl('https://example.com/path?q=1'));
    }

    public function test_rejects_empty_javascript_and_non_urls(): void
    {
        $this->assertFalse($this->shortener->isValidUrl(''));
        $this->assertFalse($this->shortener->isValidUrl('javascript:alert(1)'));
        $this->assertFalse($this->shortener->isValidUrl('not a url'));
        $this->assertFalse($this->shortener->isValidUrl('ftp://example.com'));
    }

    public function test_shorten_persists_a_code_of_expected_length(): void
    {
        $code = $this->shortener->shorten('https://example.com/long');

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{6}$/', $code);
        $this->assertSame('https://example.com/long', $this->links->find($code)['url']);
    }
}
