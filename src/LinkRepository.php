<?php

declare(strict_types=1);

namespace Src;

use PDO;
use PDOException;

final class LinkRepository
{
    public function __construct(private PDO $db)
    {
    }

    /** @throws PDOException on duplicate code (UNIQUE constraint) */
    public function create(string $code, string $url): void
    {
        $stmt = $this->db->prepare('INSERT INTO links (code, url) VALUES (:code, :url)');
        $stmt->execute(['code' => $code, 'url' => $url]);
    }

    public function find(string $code): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM links WHERE code = :code');
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function recordClick(string $code): void
    {
        $stmt = $this->db->prepare('UPDATE links SET clicks = clicks + 1 WHERE code = :code');
        $stmt->execute(['code' => $code]);
    }

    public function recent(int $limit = 10): array
    {
        $stmt = $this->db->prepare('SELECT * FROM links ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function delete(string $code): void
    {
        $stmt = $this->db->prepare('DELETE FROM links WHERE code = :code');
        $stmt->execute(['code' => $code]);
    }
}
