<?php

namespace App\Repositories;

use PDO;

class UrlCheckRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(int $urlId, string $createdAt): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO url_checks (url_id, created_at)
             VALUES (:url_id, :created_at)'
        );
        $stmt->execute([
            'url_id' => $urlId,
            'created_at' => $createdAt,
        ]);
    }

    public function findByUrlId(int $urlId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM url_checks
             WHERE url_id = :url_id
             ORDER BY id DESC'
        );

        $stmt->execute(['url_id' => $urlId]);

        return $stmt->fetchAll();
    }
}
