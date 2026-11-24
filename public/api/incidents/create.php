<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/db_connection.php';
require_once __DIR__ . '/../../../src/http.php';

handle_cors();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Use POST to submit an emergency report.'], 405);
}

try {
    $data = request_data();
    $type = trim((string)($data['emergency_type'] ?? ''));
    $description = trim((string)($data['description'] ?? ''));
    $persons = filter_var($data['persons_affected'] ?? null, FILTER_VALIDATE_INT);
    $latitude = filter_var($data['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
    $longitude = filter_var($data['longitude'] ?? null, FILTER_VALIDATE_FLOAT);
    $accuracy = filter_var($data['gps_accuracy'] ?? null, FILTER_VALIDATE_FLOAT);

    if ($type === '') json_response(['success' => false, 'message' => 'Please select a valid emergency type.'], 422);
    if ($description === '') json_response(['success' => false, 'message' => 'Please describe the incident.'], 422);
    if ($persons === false || $persons < 0) json_response(['success' => false, 'message' => 'Persons affected must be zero or a positive whole number.'], 422);
    if ($latitude === false || $latitude < -90 || $latitude > 90 || $longitude === false || $longitude < -180 || $longitude > 180) {
        json_response(['success' => false, 'message' => 'A valid latitude and longitude are required.'], 422);
    }
    if ($accuracy === false || $accuracy < 0) json_response(['success' => false, 'message' => 'A valid GPS accuracy value is required.'], 422);

    // Intended for a separate test database until the production schema is verified.
    $pdo = db_connection();
    $sql = <<<'SQL'
        INSERT INTO incidents
            (emergency_type, description, persons_affected, latitude, longitude, gps_accuracy, location)
        VALUES
            (:type, :description, :persons, :latitude, :longitude, :accuracy,
             ST_SetSRID(ST_MakePoint(:point_longitude, :point_latitude), 4326)::geography)
        RETURNING id, created_at
    SQL;
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':type' => $type, ':description' => $description, ':persons' => $persons,
        ':latitude' => $latitude, ':longitude' => $longitude, ':accuracy' => $accuracy,
        ':point_longitude' => $longitude, ':point_latitude' => $latitude,
    ]);
    json_response(['success' => true, 'message' => 'Emergency report received.', 'incident' => $stmt->fetch()], 201);
} catch (Throwable $e) {
    error_log('Incident create failed: ' . $e->getMessage());
    json_response(['success' => false, 'message' => 'The report could not be saved. Please try again later.'], 500);
}
