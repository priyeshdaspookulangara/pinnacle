<?php
// config/database.php

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $driver = getenv('DB_DRIVER') ?: 'mysql';
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $dbname = getenv('DB_NAME') ?: 'pinnacle_hunt';
            $username = getenv('DB_USER') ?: 'pinnacle_user';
            $password = getenv('DB_PASS') ?: 'Pinnacle@2026';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                // If MySQL connection fails, fallback to SQLite for seamless operation & testing
                try {
                    $sqlitePath = __DIR__ . '/../database/pinnacle_hunt.sqlite';
                    $isNewFile = !file_exists($sqlitePath);
                    self::$instance = new PDO("sqlite:" . $sqlitePath, null, null, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                    if ($isNewFile || filesize($sqlitePath) === 0) {
                        self::initSqlite(self::$instance);
                    }
                } catch (Exception $sqliteException) {
                    http_response_code(500);
                    echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Database Connection Error - Pinnacle Hunt</title>";
                    echo "<script src='https://cdn.tailwindcss.com'></script></head>";
                    echo "<body class='bg-slate-950 text-white min-h-screen flex items-center justify-center p-6'>";
                    echo "<div class='max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 text-center shadow-2xl'>";
                    echo "<div class='w-16 h-16 bg-red-500/10 text-red-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl font-bold'>!</div>";
                    echo "<h1 class='text-2xl font-bold mb-2'>Database Connection Issue</h1>";
                    echo "<p class='text-xs text-slate-400 mb-6'>Could not connect to database server.</p>";
                    echo "<div class='p-4 bg-slate-950 rounded-2xl border border-slate-800 text-left text-xs font-mono text-red-300 mb-6 overflow-x-auto'>";
                    echo htmlspecialchars($e->getMessage());
                    echo "</div>";
                    echo "</div></body></html>";
                    exit();
                }
            }
        }

        return self::$instance;
    }

    private static function initSqlite(PDO $db): void {
        $queries = [
            "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                full_name TEXT NOT NULL,
                phone TEXT DEFAULT NULL,
                role TEXT NOT NULL DEFAULT 'user',
                sponsor_id INTEGER DEFAULT NULL,
                wallet_balance REAL NOT NULL DEFAULT 0.00,
                reserve_balance REAL NOT NULL DEFAULT 0.00,
                status TEXT NOT NULL DEFAULT 'active',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (sponsor_id) REFERENCES users(id) ON DELETE SET NULL
            )",
            "CREATE TABLE IF NOT EXISTS packages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                price REAL NOT NULL,
                direct_commission_percent REAL NOT NULL DEFAULT 10.00,
                sort_order INTEGER NOT NULL DEFAULT 1,
                status TEXT NOT NULL DEFAULT 'active',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS user_packages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                package_id INTEGER NOT NULL,
                status TEXT NOT NULL DEFAULT 'active',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
                UNIQUE (user_id, package_id)
            )",
            "CREATE TABLE IF NOT EXISTS matrix_nodes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                package_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                parent_id INTEGER DEFAULT NULL,
                depth INTEGER NOT NULL DEFAULT 1,
                position_index INTEGER NOT NULL DEFAULT 1,
                children_count INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES matrix_nodes(id) ON DELETE SET NULL,
                UNIQUE (package_id, user_id)
            )",
            "CREATE TABLE IF NOT EXISTS commissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                source_user_id INTEGER NOT NULL,
                package_id INTEGER NOT NULL,
                type TEXT NOT NULL,
                level INTEGER DEFAULT NULL,
                amount REAL NOT NULL,
                reserved_amount REAL NOT NULL DEFAULT 0.00,
                net_amount REAL NOT NULL DEFAULT 0.00,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (source_user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS ledger (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                type TEXT NOT NULL,
                amount REAL NOT NULL,
                balance_after REAL NOT NULL,
                reserve_after REAL NOT NULL,
                description TEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS withdrawals (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                amount REAL NOT NULL,
                payout_details TEXT DEFAULT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                processed_at DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS settings (
                setting_key TEXT PRIMARY KEY,
                setting_value TEXT NOT NULL,
                description TEXT DEFAULT NULL
            )",
            "INSERT OR IGNORE INTO packages (id, name, price, direct_commission_percent, sort_order, status) VALUES
            (1, 'Tier 1 - Starter', 500.00, 10.00, 1, 'active'),
            (2, 'Tier 2 - Basic', 1000.00, 10.00, 2, 'active'),
            (3, 'Tier 3 - Pro', 3000.00, 10.00, 3, 'active'),
            (4, 'Tier 4 - Advanced', 5000.00, 10.00, 4, 'active'),
            (5, 'Tier 5 - Executive', 10000.00, 10.00, 5, 'active'),
            (6, 'Tier 6 - Master', 25000.00, 10.00, 6, 'active'),
            (7, 'Tier 7 - Elite', 50000.00, 10.00, 7, 'active'),
            (8, 'Tier 8 - Pinnacle', 100000.00, 10.00, 8, 'active')",
            "INSERT OR IGNORE INTO settings (setting_key, setting_value, description) VALUES
            ('reserve_percentage', '25.00', 'Percentage of earnings directed to reserve pool for auto-upgrades'),
            ('auto_upgrade_milestone_level', '3', 'Level completion milestone (1-6) that triggers package auto-progression check'),
            ('direct_sponsor_commission_percent', '10.00', 'Global direct referral commission percentage'),
            ('matrix_level_1_percent', '5.00', 'Matrix level 1 payout percentage'),
            ('matrix_level_2_percent', '4.00', 'Matrix level 2 payout percentage'),
            ('matrix_level_3_percent', '3.00', 'Matrix level 3 payout percentage'),
            ('matrix_level_4_percent', '2.00', 'Matrix level 4 payout percentage'),
            ('matrix_level_5_percent', '1.00', 'Matrix level 5 payout percentage'),
            ('matrix_level_6_percent', '1.00', 'Matrix level 6 payout percentage')",
            "INSERT OR IGNORE INTO users (id, username, email, password, full_name, phone, role, sponsor_id, wallet_balance, reserve_balance, status) VALUES
            (1, 'admin', 'admin@pinnaclehunt.com', '\$2y\$10\$6U.1K20tE10j4rGPZCB8yu30XgWUuEwxhN979tuVkQzW1/gRq2aNq', 'Pinnacle Admin', '+1000000000', 'admin', NULL, 0.00, 0.00, 'active')"
        ];

        foreach ($queries as $q) {
            $db->exec($q);
        }
    }
}
