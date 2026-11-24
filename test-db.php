<?php
declare(strict_types=1);
require_once __DIR__ . '/src/db_connection.php';
require_once __DIR__ . '/src/http.php';
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
try {
    $pdo = db_connection();
    $row = $pdo->query('SELECT version() AS version, PostGIS_Full_Version() AS postgis')->fetch();
    json_response(['success' => true, 'message' => 'Database connection successful.', 'database' => $row]);
} catch (Throwable $e) {
    error_log('Database test failed: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Database connection failed. Check the Render environment variables and PostGIS setup.'], 500);
}
