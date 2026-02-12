<?php
/**
 * Read Students API
 *
 * Methods:
 * - GET /api/read.php                -> all students (with optional pagination)
 * - GET /api/read.php?id={id}       -> single student
 * - GET /api/read.php?search=term   -> search students by name/email
 *
 * Optional pagination for list/search:
 * - page (1-based)
 * - per_page (max 100)
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Student.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_error('Method not allowed. Use GET.', 405);
}

// Establish database connection
try {
    $database = new Database();
    $db       = $database->getConnection();
} catch (Throwable $e) {
    send_error('Unable to connect to the database.', 500);
}

$student = new Student($db);

// Single student by ID
if (isset($_GET['id']) && $_GET['id'] !== '') {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        send_error('ID must be a positive integer.', 400);
    }

    $student->id = $id;

    if ($student->readOne()) {
        $studentData = [
            'id'              => $student->id,
            'first_name'      => $student->first_name,
            'last_name'       => $student->last_name,
            'email'           => $student->email,
            'phone'           => $student->phone,
            'date_of_birth'   => $student->date_of_birth,
            'gender'          => $student->gender,
            'address'         => $student->address,
            'enrollment_date' => $student->enrollment_date,
            'created_at'      => $student->created_at,
            'updated_at'      => $student->updated_at,
        ];

        send_success($studentData);
    }

    send_error('Student not found.', 404);
}

// Pagination parameters for collections
$page    = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
$perPage = isset($_GET['per_page']) ? min(max((int) $_GET['per_page'], 1), 100) : 50;
$offset  = ($page - 1) * $perPage;

// Search
if (isset($_GET['search']) && $_GET['search'] !== '') {
    $keywords = trim((string) $_GET['search']);

    $stmt = $student->search($keywords, $perPage, $offset);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) === 0) {
        send_error('No students found.', 404);
    }

    $response = [
        'count'    => count($rows),
        'students' => $rows,
        'page'     => $page,
        'per_page' => $perPage,
    ];

    send_success($response);
}

// All students
$stmt = $student->readAll($perPage, $offset);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) === 0) {
    send_error('No students found.', 404);
}

$response = [
    'count'    => count($rows),
    'students' => $rows,
    'page'     => $page,
    'per_page' => $perPage,
];

send_success($response);

