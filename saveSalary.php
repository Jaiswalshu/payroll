<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Clear any previous output
ob_clean();

$data = json_decode(file_get_contents('php://input'), true);

$emp_id = $data['Emp_ID'] ?? null;
$basic_salary = $data['Basic_salary'] ?? null;
$allowance = $data['Allowance'] ?? null;
$bonus = $data['Bonus'] ?? 0;

// Validate required fields
if (!$emp_id || !$basic_salary || !$allowance) {
    echo json_encode(['success' => false, 'message' => 'Employee ID, Basic Salary, and Allowance are required']);
    exit;
}

// Check if employee exists
$check = $conn->prepare("SELECT Emp_ID FROM Employee WHERE Emp_ID = ?");
$check->bind_param("i", $emp_id);
$check->execute();
$check->store_result();

if ($check->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Employee not found']);
    exit;
}

// Check if salary record exists
$check = $conn->prepare("SELECT Emp_ID FROM Salary WHERE Emp_ID = ?");
$check->bind_param("i", $emp_id);
$check->execute();
$check->store_result();

$response = [];

if ($check->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE Salary SET Basic_salary = ?, Allowance = ?, Bonus = ? WHERE Emp_ID = ?");
    $stmt->bind_param("dddi", $basic_salary, $allowance, $bonus, $emp_id);
} else {
    $stmt = $conn->prepare("INSERT INTO Salary (Emp_ID, Basic_salary, Allowance, Bonus) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iddd", $emp_id, $basic_salary, $allowance, $bonus);
}

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Salary information saved successfully';
} else {
    $response['success'] = false;
    $response['message'] = 'Failed to save salary: ' . $conn->error;
}

echo json_encode($response);
$conn->close();
?>