<?php
/**
 * Create Student API
 *
 * Method: POST
 * URL:   /api/create.php
 *
 * JSON body:
 * {
 *   "first_name": "John",
 *   "last_name": "Doe",
 *   "email": "john.doe@example.com",
 *   "phone": "...",                // optional
 *   "date_of_birth": "2000-01-01", // optional, YYYY-MM-DD
 *   "gender": "Male",              // optional: Male|Female|Other
 *   "address": "...",              // optional
 *   "enrollment_date": "2024-01-01"// optional, defaults to today
 * }
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Student.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Method not allowed. Use POST.', 405);
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

// Validate input
$validation = $student->validate($data, false);
if (!$validation['valid']) {
    send_error('Validation failed.', 422, $validation['errors']);
}

$sanitized = $validation['data'];

// Check for duplicate email
if ($student->emailExists($sanitized['email'])) {
    send_error('Email already exists.', 409);
}

// Fill model and persist
$student->fill($sanitized);

if ($student->create()) {
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

    send_success($responseData, 'Student created successfully.', 201);
}

send_error('Unable to create student.', 500);

