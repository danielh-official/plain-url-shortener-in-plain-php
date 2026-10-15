<?php

declare(strict_types=1);

namespace Src;

/** Ends the request with a status, optional body, and headers (used instead of exit). */
final class HttpFinish extends \Exception
{
    /** @param array<string, string> $headers */
    public function __construct(
        public readonly int $status = 200,
        public readonly string $body = '',
        public readonly array $headers = [],
    ) {
        parent::__construct($body, $status);
    }
}
