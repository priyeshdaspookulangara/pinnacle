<?php
// config/app.php

// Enable error display for diagnostics or handle exceptions cleanly
ini_set('display_errors', '0');
error_reporting(E_ALL);

set_exception_handler(function ($e) {
    http_response_code(500);
    echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Pinnacle Hunt - Server Notice</title>";
    echo "<script src='https://cdn.tailwindcss.com'></script></head>";
    echo "<body class='bg-slate-950 text-white min-h-screen flex items-center justify-center p-6'>";
    echo "<div class='max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 text-center shadow-2xl'>";
    echo "<div class='w-16 h-16 bg-red-500/10 text-red-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl font-bold'>!</div>";
    echo "<h1 class='text-2xl font-bold mb-2'>System Application Error</h1>";
    echo "<p class='text-xs text-slate-400 mb-6'>An unexpected server exception occurred while processing your request.</p>";
    echo "<div class='p-4 bg-slate-950 rounded-2xl border border-slate-800 text-left text-xs font-mono text-red-300 mb-6 overflow-x-auto'>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
    echo "<a href='index.php?page=login' class='inline-block px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-black font-bold rounded-2xl text-xs transition'>Return to Login Page</a>";
    echo "</div></body></html>";
    exit();
});

if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        ob_start();
        session_start();
    }
}

define('APP_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000'));

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
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id, username, email, full_name, phone, role, sponsor_id, wallet_balance, reserve_balance, status, created_at FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        } catch (Exception $e) {
            $user = null;
        }
    }
    return $user ?: null;
}

// Helper: Require auth
function requireAuth(?string $requiredRole = null) {
    if (!isLoggedIn()) {
        header("Location: index.php?page=login");
        exit();
    }
    if ($requiredRole !== null) {
        $user = currentUser();
        if (!$user || $user['role'] !== $requiredRole) {
            header("Location: index.php?page=dashboard&error=Unauthorized");
            exit();
        }
    }
}

// Helper: Get setting
function getSetting(string $key, $default = null) {
    static $settingsCache = null;
    if ($settingsCache === null) {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $settingsCache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            $settingsCache = [];
        }
    }
    return $settingsCache[$key] ?? $default;
}

// Helper: Format Currency (INR ₹)
function formatCurrency($amount): string {
    return '₹' . number_format((float)$amount, 2);
}
