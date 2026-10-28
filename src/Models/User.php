<?php
// src/Models/User.php

require_once __DIR__ . '/../../config/database.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function register(string $username, string $email, string $password, string $fullName, ?string $phone, ?int $sponsorId): int {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, full_name, phone, role, sponsor_id, wallet_balance, reserve_balance, status)
            VALUES (?, ?, ?, ?, ?, 'user', ?, 0.00, 0.00, 'active')
        ");
        $stmt->execute([$username, $email, $hash, $fullName, $phone, $sponsorId]);
        return (int)$this->db->lastInsertId();
    }

    public function authenticate(string $login, string $password): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    public function updateBalances(int $userId, float $walletDelta, float $reserveDelta): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET wallet_balance = wallet_balance + ?, reserve_balance = reserve_balance + ?
            WHERE id = ?
        ");
        return $stmt->execute([$walletDelta, $reserveDelta, $userId]);
    }

    public function getAllUsers(): array {
        $stmt = $this->db->query("
            SELECT u.*, s.username as sponsor_username
            FROM users u
            LEFT JOIN users s ON u.sponsor_id = s.id
            ORDER BY u.id DESC
        ");
        return $stmt->fetchAll();
    }
}
