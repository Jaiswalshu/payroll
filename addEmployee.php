<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Clear any previous output
ob_clean();

$data = json_decode(file_get_contents('php://input'), true);

$emp_id = $data['Emp_ID'] ?? null;
$first_name = $data['First_name'] ?? null;
$last_name = $data['Last_name'] ?? null;
$dob = $data['DOB'] ?? null;
$hire_date = $data['Hire_date'] ?? null;
$department = $data['Department'] ?? null;
$position = $data['Position'] ?? null;

// Validate required fields
if (!$emp_id || !$first_name || !$last_name || !$dob || !$hire_date || !$department || !$position) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Check for duplicate Emp_ID
$check = $conn->prepare("SELECT Emp_ID FROM Employee WHERE Emp_ID = ?");
$check->bind_param("i", $emp_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Employee ID already exists']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO Employee (Emp_ID, First_name, Last_name, DOB, Hire_date, Department, Position) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssss", $emp_id, $first_name, $last_name, $dob, $hire_date, $department, $position);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Employee added successfully';
} else {
    $response['success'] = false;
    $response['message'] = 'Failed to add employee: ' . $conn->error;
}

echo json_encode($response);
$conn->close();
?>