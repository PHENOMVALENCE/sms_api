<?php
/**
 * Create Student API
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/Student.php';

// Initialize database and student object
$database = new Database();
$db = $database->getConnection();
$student = new Student($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if(
    !empty($data->first_name) &&
    !empty($data->last_name) &&
    !empty($data->email)
) {
    // Set student properties
    $student->first_name = $data->first_name;
    $student->last_name = $data->last_name;
    $student->email = $data->email;
    $student->phone = isset($data->phone) ? $data->phone : '';
    $student->date_of_birth = isset($data->date_of_birth) ? $data->date_of_birth : null;
    $student->gender = isset($data->gender) ? $data->gender : '';
    $student->address = isset($data->address) ? $data->address : '';
    $student->enrollment_date = isset($data->enrollment_date) ? $data->enrollment_date : date('Y-m-d');

    // Check if email already exists
    if($student->emailExists()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists.'
        ]);
    }
    // Create the student
    else if($student->create()) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Student created successfully.',
            'data' => [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'phone' => $student->phone,
                'date_of_birth' => $student->date_of_birth,
                'gender' => $student->gender,
                'address' => $student->address,
                'enrollment_date' => $student->enrollment_date
            ]
        ]);
    }
    // Unable to create student
    else {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'message' => 'Unable to create student.'
        ]);
    }
}
// Required fields are missing
else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to create student. Required fields are missing.',
        'required_fields' => ['first_name', 'last_name', 'email']
    ]);
}
?>
