<?php
/**
 * API Test Examples using cURL
 * This file demonstrates how to test the Student Management API
 */

// Base URL - Update this with your actual API URL
$base_url = "http://localhost/student-management-api/api";

echo "=== Student Management API Test Examples ===\n\n";

// 1. CREATE - Add a new student
echo "1. CREATE Student:\n";
$create_data = [
    'first_name' => 'Alice',
    'last_name' => 'Williams',
    'email' => 'alice.williams@example.com',
    'phone' => '555-0106',
    'date_of_birth' => '2001-06-20',
    'gender' => 'Female',
    'address' => '987 Cedar Lane, TestCity, USA',
    'enrollment_date' => '2024-02-01'
];

$ch = curl_init($base_url . "/create.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($create_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);
echo $response . "\n\n";

// 2. READ - Get all students
echo "2. READ All Students:\n";
$ch = curl_init($base_url . "/read.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo $response . "\n\n";

// 3. READ - Get single student by ID
echo "3. READ Single Student (ID: 1):\n";
$ch = curl_init($base_url . "/read.php?id=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo $response . "\n\n";

// 4. READ - Search students
echo "4. SEARCH Students (keyword: 'John'):\n";
$ch = curl_init($base_url . "/read.php?search=John");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo $response . "\n\n";

// 5. UPDATE - Update student details
echo "5. UPDATE Student (ID: 1):\n";
$update_data = [
    'id' => 1,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe.updated@example.com',
    'phone' => '555-9999',
    'date_of_birth' => '2000-05-15',
    'gender' => 'Male',
    'address' => '123 Updated St, Anytown, USA',
    'enrollment_date' => '2024-01-15'
];

$ch = curl_init($base_url . "/update.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);
echo $response . "\n\n";

// 6. DELETE - Delete a student
echo "6. DELETE Student (ID: 1):\n";
$delete_data = ['id' => 1];

$ch = curl_init($base_url . "/delete.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($delete_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);
echo $response . "\n\n";

echo "=== Test Complete ===\n";

/* 
 * ALTERNATIVE: Using JavaScript fetch() for testing in browser console
 * 
 * // Create Student
 * fetch('http://localhost/student-management-api/api/create.php', {
 *     method: 'POST',
 *     headers: { 'Content-Type': 'application/json' },
 *     body: JSON.stringify({
 *         first_name: 'Bob',
 *         last_name: 'Test',
 *         email: 'bob.test@example.com',
 *         phone: '555-0200',
 *         date_of_birth: '2000-01-01',
 *         gender: 'Male',
 *         address: '123 Test St',
 *         enrollment_date: '2024-02-01'
 *     })
 * }).then(r => r.json()).then(console.log);
 * 
 * // Read All Students
 * fetch('http://localhost/student-management-api/api/read.php')
 *     .then(r => r.json()).then(console.log);
 * 
 * // Read Single Student
 * fetch('http://localhost/student-management-api/api/read.php?id=1')
 *     .then(r => r.json()).then(console.log);
 * 
 * // Search Students
 * fetch('http://localhost/student-management-api/api/read.php?search=John')
 *     .then(r => r.json()).then(console.log);
 * 
 * // Update Student
 * fetch('http://localhost/student-management-api/api/update.php', {
 *     method: 'PUT',
 *     headers: { 'Content-Type': 'application/json' },
 *     body: JSON.stringify({
 *         id: 1,
 *         first_name: 'John Updated',
 *         last_name: 'Doe',
 *         email: 'john.doe@example.com',
 *         phone: '555-0101',
 *         date_of_birth: '2000-05-15',
 *         gender: 'Male',
 *         address: '123 Main St',
 *         enrollment_date: '2024-01-15'
 *     })
 * }).then(r => r.json()).then(console.log);
 * 
 * // Delete Student
 * fetch('http://localhost/student-management-api/api/delete.php', {
 *     method: 'DELETE',
 *     headers: { 'Content-Type': 'application/json' },
 *     body: JSON.stringify({ id: 1 })
 * }).then(r => r.json()).then(console.log);
 */
?>
