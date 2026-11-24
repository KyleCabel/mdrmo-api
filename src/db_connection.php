<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';
function db_connection(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $cfg = database_config();
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s;sslmode=require', $cfg['DB_HOST'], $cfg['DB_PORT'], $cfg['DB_NAME']);
    $pdo = new PDO($dsn, $cfg['DB_USER'], $cfg['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
