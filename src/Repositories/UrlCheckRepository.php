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

    public function create(
        int $urlId,
        int $statusCode,
        ?string $h1,
        ?string $title,
        ?string $description,
        string $createdAt
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO url_checks (
                url_id, status_code, h1, title, description, created_at
            )
             VALUES (
                :url_id, :status_code, :h1, :title, :description, :created_at
            )'
        );

        $stmt->execute([
            'url_id' => $urlId,
            'status_code' => $statusCode,
            'h1' => $h1,
            'title' => $title,
            'description' => $description,
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

    public function findLatestChecks(): array
    {
        $stmt = $this->pdo->query(
            'SELECT DISTINCT ON (url_id)
                url_id,
                status_code,
                created_at AS last_check_at
                FROM url_checks
            ORDER BY url_id, created_at DESC'
        );

        return $stmt->fetchAll();
    }
}
