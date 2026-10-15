<?php

declare(strict_types=1);

namespace Tests;

final class LinkRepositoryTest extends TestCase
{
    public function test_create_and_find_round_trip(): void
    {
        $this->links->create('abc123', 'https://example.com');

        $link = $this->links->find('abc123');

        $this->assertNotNull($link);
        $this->assertSame('abc123', $link['code']);
        $this->assertSame('https://example.com', $link['url']);
        $this->assertSame(0, (int) $link['clicks']);
    }

    public function test_find_returns_null_for_unknown_code(): void
    {
        $this->assertNull($this->links->find('missing'));
    }

    public function test_record_click_increments_counter(): void
    {
        $this->links->create('abc123', 'https://example.com');

        $this->links->recordClick('abc123');
        $this->links->recordClick('abc123');

        $this->assertSame(2, (int) $this->links->find('abc123')['clicks']);
    }

    public function test_recent_returns_newest_first(): void
    {
        $this->links->create('old001', 'https://example.com/old');
        $this->links->create('new001', 'https://example.com/new');

        $recent = $this->links->recent(10);

        $this->assertSame('new001', $recent[0]['code']);
        $this->assertSame('old001', $recent[1]['code']);
    }

    public function test_delete_removes_link(): void
    {
        $this->links->create('abc123', 'https://example.com');

        $this->links->delete('abc123');

        $this->assertNull($this->links->find('abc123'));
    }
}
