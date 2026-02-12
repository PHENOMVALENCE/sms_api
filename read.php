<?php
/**
 * Read Students API
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';
include_once '../models/Student.php';

// Initialize database and student object
$database = new Database();
$db = $database->getConnection();
$student = new Student($db);

// Check if ID is provided for single student
if(isset($_GET['id']) && !empty($_GET['id'])) {
    // Get single student
    $student->id = $_GET['id'];
    
    if($student->readOne()) {
        $student_data = [
            'id' => $student->id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'phone' => $student->phone,
            'date_of_birth' => $student->date_of_birth,
            'gender' => $student->gender,
            'address' => $student->address,
            'enrollment_date' => $student->enrollment_date,
            'created_at' => $student->created_at,
            'updated_at' => $student->updated_at
        ];

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $student_data
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Student not found.'
        ]);
    }
}
// Check if search parameter is provided
else if(isset($_GET['search']) && !empty($_GET['search'])) {
    // Search students
    $stmt = $student->search($_GET['search']);
    $num = $stmt->rowCount();

    if($num > 0) {
        $students_arr = [];
        $students_arr['count'] = $num;
        $students_arr['students'] = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $student_item = [
                'id' => $id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'address' => $address,
                'enrollment_date' => $enrollment_date,
                'created_at' => $created_at,
                'updated_at' => $updated_at
            ];
            array_push($students_arr['students'], $student_item);
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $students_arr
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No students found.'
        ]);
    }
}
// Get all students
else {
    $stmt = $student->readAll();
    $num = $stmt->rowCount();

    if($num > 0) {
        $students_arr = [];
        $students_arr['count'] = $num;
        $students_arr['students'] = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $student_item = [
                'id' => $id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'address' => $address,
                'enrollment_date' => $enrollment_date,
                'created_at' => $created_at,
                'updated_at' => $updated_at
            ];
            array_push($students_arr['students'], $student_item);
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $students_arr
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No students found.'
        ]);
    }
}
?>
