<?php
/**
 * Delete Student API
 *
 * Method: DELETE
 * URL:    /api/delete.php
 *
 * JSON body:
 * {
 *   "id": 1
 * }
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Student.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    send_error('Method not allowed. Use DELETE.', 405);
}

// Decode JSON payload
$rawBody = file_get_contents('php://input');
$data    = json_decode($rawBody, true);

if (!is_array($data)) {
    send_error('Invalid JSON payload.', 400);
}

if (!isset($data['id'])) {
    send_error('ID is required.', 400);
}

$id = filter_var($data['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    send_error('ID must be a positive integer.', 400);
}

// Establish database connection
try {
    $database = new Database();
    $db       = $database->getConnection();
} catch (Throwable $e) {
    send_error('Unable to connect to the database.', 500);
}

$student       = new Student($db);
$student->id   = $id;

// Ensure the student exists
if (!$student->readOne()) {
    send_error('Student not found.', 404);
}

if ($student->delete()) {
    send_success(['id' => $id], 'Student deleted successfully.', 200);
}

send_error('Unable to delete student.', 500);

