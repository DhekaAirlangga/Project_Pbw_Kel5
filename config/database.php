<?php
// ==========================================
// config/database.php
// Koneksi database menggunakan PDO
// Membaca konfigurasi dari .env
// ==========================================

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key]     = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

// Load .env dari root project
loadEnv(__DIR__ . '/../.env');

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host   = $_ENV['DB_HOST']   ?? 'localhost';
    $port   = $_ENV['DB_PORT']   ?? '3306';
    $name   = $_ENV['DB_NAME']   ?? 'parkirku_up';
    $user   = $_ENV['DB_USER']   ?? 'root';
    $pass   = $_ENV['DB_PASS']   ?? '';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Tampilkan pesan ramah, jangan expose detail koneksi
        $msg = ($_ENV['APP_DEBUG'] ?? 'false') === 'true'
            ? 'Koneksi database gagal: ' . $e->getMessage()
            : 'Koneksi database gagal. Hubungi administrator.';
        die("<div style='font-family:sans-serif;padding:20px;color:#b91c1c;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;margin:20px;'>
            <strong>⚠ Database Error</strong><br>{$msg}
        </div>");
    }

    return $pdo;
}
