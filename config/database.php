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
}
