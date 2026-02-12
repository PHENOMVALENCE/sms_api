<?php
/**
 * Update Student API
 *
 * Method: PUT
 * URL:    /api/update.php
 *
 * JSON body:
 * {
 *   "id": 1,
 *   "first_name": "John",
 *   "last_name": "Doe",
 *   "email": "john.doe@example.com",
 *   "phone": "...",
 *   "date_of_birth": "2000-01-01",
 *   "gender": "Male",
 *   "address": "...",
 *   "enrollment_date": "2024-01-01"
 * }
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Student.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    send_error('Method not allowed. Use PUT.', 405);
}

// Decode JSON payload
$rawBody = file_get_contents('php://input');
$data    = json_decode($rawBody, true);

if (!is_array($data)) {
    send_error('Invalid JSON payload.', 400);
}

// Establish database connection
try {
    $database = new Database();
    $db       = $database->getConnection();
} catch (Throwable $e) {
    send_error('Unable to connect to the database.', 500);
}

$student = new Student($db);

// Validate input (update mode)
$validation = $student->validate($data, true);
if (!$validation['valid']) {
    send_error('Validation failed.', 422, $validation['errors']);
}

$sanitized = $validation['data'];
$studentId = $sanitized['id'] ?? null;

// Ensure the student exists
$student->id = $studentId;
if (!$student->readOne()) {
    send_error('Student not found.', 404);
}

// Check if email already exists for another student
if ($student->emailExists($sanitized['email'], $studentId)) {
    send_error('Email already exists.', 409);
}

// Fill with sanitized data (id included)
$student->fill($sanitized);

if ($student->update()) {
    $responseData = [
        'id'              => $student->id,
        'first_name'      => $student->first_name,
        'last_name'       => $student->last_name,
        'email'           => $student->email,
        'phone'           => $student->phone,
        'date_of_birth'   => $student->date_of_birth,
        'gender'          => $student->gender,
        'address'         => $student->address,
        'enrollment_date' => $student->enrollment_date,
    ];

    send_success($responseData, 'Student updated successfully.', 200);
}

send_error('Unable to update student.', 500);

