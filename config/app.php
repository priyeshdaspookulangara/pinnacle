<?php
// config/app.php

if (session_status() === PHP_SESSION_NONE) {
    ob_start();
    session_start();
}

require_once __DIR__ . '/database.php';

// Helper: Generate CSRF token
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Helper: Verify CSRF token
function verifyCsrfToken(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Helper: Check if user is logged in
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

// Helper: Get logged in user array
function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    static $user = null;
    if ($user === null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, username, email, full_name, phone, role, sponsor_id, wallet_balance, reserve_balance, status, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    return $user ?: null;
}

// Helper: Require auth
function requireAuth(?string $requiredRole = null) {
    if (!isLoggedIn()) {
        header("Location: /index.php?page=login");
        exit();
    }
    if ($requiredRole !== null) {
        $user = currentUser();
        if (!$user || $user['role'] !== $requiredRole) {
            header("Location: /index.php?page=dashboard&error=Unauthorized");
            exit();
        }
    }
}

// Helper: Get setting
function getSetting(string $key, $default = null) {
    static $settingsCache = null;
    if ($settingsCache === null) {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settingsCache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $settingsCache[$key] ?? $default;
}

// Helper: Format Currency (INR ₹)
function formatCurrency($amount): string {
    return '₹' . number_format((float)$amount, 2);
}
