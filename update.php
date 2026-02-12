<?php
/**
 * Update Student API
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
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
    !empty($data->id) &&
    !empty($data->first_name) &&
    !empty($data->last_name) &&
    !empty($data->email)
) {
    // Set ID to update
    $student->id = $data->id;

    // Check if student exists
    if(!$student->readOne()) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Student not found.'
        ]);
        exit;
    }

    // Set student properties
    $student->first_name = $data->first_name;
    $student->last_name = $data->last_name;
    $student->email = $data->email;
    $student->phone = isset($data->phone) ? $data->phone : '';
    $student->date_of_birth = isset($data->date_of_birth) ? $data->date_of_birth : null;
    $student->gender = isset($data->gender) ? $data->gender : '';
    $student->address = isset($data->address) ? $data->address : '';
    $student->enrollment_date = isset($data->enrollment_date) ? $data->enrollment_date : null;

    // Check if email already exists for another student
    if($student->emailExists()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists.'
        ]);
    }
    // Update the student
    else if($student->update()) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Student updated successfully.',
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
    // Unable to update student
    else {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'message' => 'Unable to update student.'
        ]);
    }
}
// Required fields are missing
else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to update student. Required fields are missing.',
        'required_fields' => ['id', 'first_name', 'last_name', 'email']
    ]);
}
?>
