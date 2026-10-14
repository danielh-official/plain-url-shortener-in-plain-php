<?php

declare(strict_types=1);

namespace Src;

use RuntimeException;
use PDOException;

final class Shortener
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const MAX_URL_LENGTH = 2048;
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private LinkRepository $links,
        private int $codeLength = 6,
    ) {
    }

    /** Only absolute http(s) URLs are accepted, which blocks javascript: and data: links. */
    public function isValidUrl(string $url): bool
    {
        if ($url === '' || strlen($url) > self::MAX_URL_LENGTH) {
            return false;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }

    /** Stores the URL and returns its short code. */
    public function shorten(string $url): string
    {
        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            $code = $this->generateCode();

            try {
                $this->links->create($code, $url);

                return $code;
            } catch (PDOException $e) {
                // 23000 = integrity constraint violation (code collision). Retry with a new code.
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }

        throw new RuntimeException('Could not generate a unique code.');
    }

    private function generateCode(): string
    {
        $max  = strlen(self::ALPHABET) - 1;
        $code = '';

        for ($i = 0; $i < $this->codeLength; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        return $code;
    }
}
