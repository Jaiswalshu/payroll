<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Clear any previous output
ob_clean();

$recent = isset($_GET['recent']) ? true : false;

$query = "SELECT p.Payroll_ID, p.Emp_ID, p.Payroll_Date, p.Total_salary, 
                 e.First_name, e.Last_name, 
                 COALESCE(d.Tax, 0) AS Tax, 
                 COALESCE(d.Insurance, 0) AS Insurance,
                 (SELECT COUNT(*) FROM Payment WHERE Payroll_ID = p.Payroll_ID) AS payment_count
          FROM Payroll p
          JOIN Employee e ON p.Emp_ID = e.Emp_ID
          LEFT JOIN Deductions d ON p.Emp_ID = d.Emp_ID";

if ($recent) {
    $query .= " ORDER BY p.Payroll_Date DESC LIMIT 10";
}

$result = $conn->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve payroll data: ' . $conn->error]);
    exit;
}

$payrollData = [];
while ($row = $result->fetch_assoc()) {
    $row['Net_salary'] = $row['Total_salary'] - ($row['Tax'] + $row['Insurance']);
    $row['payment_status'] = $row['payment_count'] > 0 ? 'Paid' : 'Pending';
    unset($row['payment_count']);
    $payrollData[] = $row;
}

echo json_encode(['success' => true, 'data' => $payrollData], JSON_NUMERIC_CHECK);
$conn->close();
?>