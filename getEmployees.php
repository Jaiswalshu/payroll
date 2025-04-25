<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Clear any previous output
ob_clean();

$query = "SELECT Emp_ID, First_name, Last_name, DOB, Hire_date, Department, Position FROM Employee";
$result = $conn->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve employees: ' . $conn->error]);
    exit;
}

$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}

echo json_encode(['success' => true, 'data' => $employees]);
$conn->close();
?>