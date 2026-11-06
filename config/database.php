<?php
// config/database.php

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
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
                // Ensure required tables and columns exist automatically on remote/live servers
                self::ensureTables(self::$instance);
            } catch (PDOException $e) {
                http_response_code(500);
                echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Database Connection Error - Pinnacle Hunt</title>";
                echo "<script src='https://cdn.tailwindcss.com'></script></head>";
                echo "<body class='bg-slate-950 text-white min-h-screen flex items-center justify-center p-6'>";
                echo "<div class='max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 text-center shadow-2xl'>";
                echo "<div class='w-16 h-16 bg-red-500/10 text-red-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl font-bold'>!</div>";
                echo "<h1 class='text-2xl font-bold mb-2'>Database Connection Issue</h1>";
                echo "<p class='text-xs text-slate-400 mb-6'>Could not connect to MySQL database server using configured credentials.</p>";
                echo "<div class='p-4 bg-slate-950 rounded-2xl border border-slate-800 text-left text-xs font-mono text-red-300 mb-6 overflow-x-auto'>";
                echo htmlspecialchars($e->getMessage());
                echo "</div>";
                echo "<div class='text-xs text-slate-400 text-left space-y-2 mb-6'>";
                echo "<p class='font-bold text-slate-200'>Troubleshooting Steps:</p>";
                echo "<ul class='list-disc list-inside space-y-1'>";
                echo "<li>Ensure MariaDB/MySQL service is running.</li>";
                echo "<li>Verify database <code class='text-emerald-400'>pinnacle_hunt</code> exists and schema is imported from <code class='text-emerald-400'>database/schema.sql</code>.</li>";
                echo "<li>Set environment variables <code class='text-emerald-400'>DB_HOST, DB_USER, DB_PASS, DB_NAME</code> or update <code class='text-emerald-400'>config/database.php</code>.</li>";
                echo "</ul></div>";
                echo "</div></body></html>";
                exit();
            }
        }

        return self::$instance;
    }

    /**
     * Automatically ensure required tables and columns exist
     * to prevent 'Table doesn't exist' or missing column errors on deployed servers
     */
    private static function ensureTables(PDO $pdo): void {
        try {
            // 1. Ensure usdt_payments table exists
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `usdt_payments` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `package_id` INT NOT NULL,
                    `amount_inr` DECIMAL(15,2) NOT NULL,
                    `amount_usdt` DECIMAL(15,2) NOT NULL,
                    `network` VARCHAR(20) NOT NULL DEFAULT 'TRC20',
                    `wallet_address` VARCHAR(255) NOT NULL,
                    `txid` VARCHAR(255) DEFAULT NULL,
                    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                    `admin_notes` TEXT DEFAULT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 2. Ensure users table has USDT personal columns
            $cols = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'usdt_trc20_address'")->fetchAll();
            if (empty($cols)) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `usdt_trc20_address` VARCHAR(120) NULL AFTER `phone`");
            }

            $colsBep = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'usdt_bep20_address'")->fetchAll();
            if (empty($colsBep)) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `usdt_bep20_address` VARCHAR(120) NULL AFTER `usdt_trc20_address`");
            }

            $colsQr = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'usdt_qr_image'")->fetchAll();
            if (empty($colsQr)) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `usdt_qr_image` VARCHAR(255) NULL AFTER `usdt_bep20_address`");
            }

            // 3. Ensure corporate USDT settings exist
            $pdo->exec("
                INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
                ('usdt_trc20_address', 'TYDzpSLtq8X2yB33x9v2QG2H1234567890', 'USDT TRC20 Deposit Wallet Address'),
                ('usdt_bep20_address', '0x572790c26FC062C247D0e1797D646F20a093AE14', 'USDT BEP20 Deposit Wallet Address'),
                ('usdt_trc20_qr', 'public/assets/images/usdt_bep20_qr_clean.png', 'USDT TRC20 QR Code Image'),
                ('usdt_bep20_qr', 'public/assets/images/usdt_bep20_qr_clean.png', 'USDT BEP20 QR Code Image'),
                ('usdt_inr_rate', '90.00', '1 USDT conversion rate in INR (₹)');
            ");
        } catch (Throwable $t) {
            // Silently allow if permissions prevent DDL
        }
    }
}
