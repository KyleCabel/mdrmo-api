<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../src/db_connection.php';
require_once __DIR__ . '/../../../src/http.php';
handle_cors();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['success' => false, 'message' => 'Use GET to view an incident report.'], 405);
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) json_response(['success' => false, 'message' => 'A valid incident id is required.'], 422);
try {
    $pdo = db_connection();
    $stmt = $pdo->prepare('SELECT id, emergency_type, description, persons_affected, latitude, longitude, gps_accuracy, created_at FROM incidents WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $incident = $stmt->fetch();
    if (!$incident) json_response(['success' => false, 'message' => 'Incident report not found.'], 404);
    json_response(['success' => true, 'incident' => $incident]);
} catch (Throwable $e) {
    error_log('Incident view failed: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'Unable to retrieve the incident report.'], 500);
}
