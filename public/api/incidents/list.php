<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../src/db_connection.php';
require_once __DIR__ . '/../../../src/http.php';
handle_cors();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['success' => false, 'message' => 'Use GET to list incident reports.'], 405);
try {
    $limit = filter_var($_GET['limit'] ?? 50, FILTER_VALIDATE_INT);
    $limit = max(1, min($limit ?: 50, 100));
    $offset = filter_var($_GET['offset'] ?? 0, FILTER_VALIDATE_INT);
    $offset = max(0, $offset ?: 0);
    $pdo = db_connection();
    $stmt = $pdo->prepare('SELECT id, emergency_type, description, persons_affected, latitude, longitude, gps_accuracy, created_at FROM incidents ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    json_response(['success' => true, 'count' => $stmt->rowCount(), 'incidents' => $stmt->fetchAll()]);
} catch (Throwable $e) {
    error_log('Incident list failed: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Unable to retrieve incident reports.'], 500);
}
