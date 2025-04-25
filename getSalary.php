<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Clear any previous output
ob_clean();

$query = "SELECT e.Emp_ID, e.First_name, e.Last_name, e.Department, 
                 s.Basic_salary, s.Allowance, s.Bonus 
          FROM Employee e
          LEFT JOIN Salary s ON e.Emp_ID = s.Emp_ID";
$result = $conn->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve salary data: ' . $conn->error]);
    exit;
}

$salaryData = [];
while ($row = $result->fetch_assoc()) {
    $salaryData[] = $row;
}

echo json_encode(['success' => true, 'data' => $salaryData]);
$conn->close();
?>