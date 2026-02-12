<?php
/**
 * Delete Student API
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
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

// Validate ID
if(!empty($data->id)) {
    // Set ID to delete
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

    // Delete the student
    if($student->delete()) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Student deleted successfully.',
            'data' => [
                'id' => $data->id
            ]
        ]);
    }
    // Unable to delete student
    else {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'message' => 'Unable to delete student.'
        ]);
    }
}
// ID is missing
else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to delete student. ID is required.'
    ]);
}
?>
