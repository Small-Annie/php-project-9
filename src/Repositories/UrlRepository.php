<?php

namespace App\Repositories;

use PDO;

class UrlRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $name, string $createdAt): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO urls (name, created_at)
             VALUES (:name, :created_at)
             RETURNING id'
        );

        $stmt->execute([
            'name' => $name,
            'created_at' => $createdAt
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM urls WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM urls WHERE name = :name'
        );
        $stmt->execute(['name' => $name]);

        return $stmt->fetch() ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM urls ORDER BY created_at DESC'
        );

        return $stmt->fetchAll();
    }
}
