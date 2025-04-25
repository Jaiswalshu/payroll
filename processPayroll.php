<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Clear any previous output
ob_clean();

$data = json_decode(file_get_contents('php://input'), true);

$emp_id = $data['Emp_ID'] ?? null;
$payroll_date = $data['Payroll_Date'] ?? null;
$tax = $data['Tax'] ?? 0;
$insurance = $data['Insurance'] ?? 0;

// Validate required fields
if (!$emp_id || !$payroll_date) {
    echo json_encode(['success' => false, 'message' => 'Employee ID and Payroll Date are required']);
    exit;
}

// Get employee salary
$salary_query = "SELECT Basic_salary, Allowance, Bonus FROM Salary WHERE Emp_ID = ?";
$stmt = $conn->prepare($salary_query);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$salary_result = $stmt->get_result();

if ($salary_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Salary information not found']);
    exit;
}

$salary = $salary_result->fetch_assoc();

// Calculate payroll
$basic_salary = (float)$salary['Basic_salary'];
$allowance = (float)$salary['Allowance'];
$bonus = (float)($salary['Bonus'] ?? 0);
$gross_salary = $basic_salary + $allowance + $bonus;
$total_deductions = (float)$tax + (float)$insurance;
$net_salary = $gross_salary - $total_deductions;

// Insert payroll record
$payroll_query = "INSERT INTO Payroll (Emp_ID, Payroll_Date, Total_salary) VALUES (?, ?, ?)";
$stmt = $conn->prepare($payroll_query);
$stmt->bind_param("isd", $emp_id, $payroll_date, $gross_salary);

$response = [];

if ($stmt->execute()) {
    $payroll_id = $conn->insert_id;
    
    // Insert or update deductions
    $deductions_query = "INSERT INTO Deductions (Emp_ID, Tax, Insurance) VALUES (?, ?, ?) 
                         ON DUPLICATE KEY UPDATE Tax = ?, Insurance = ?";
    $stmt = $conn->prepare($deductions_query);
    $stmt->bind_param("idddd", $emp_id, $tax, $insurance, $tax, $insurance);
    $stmt->execute();
    
    // Insert payment record to mark payroll as Paid
    $payment_date = date('Y-m-d'); // Use current date for payment
    $payment_query = "INSERT INTO Payment (Payroll_ID, Payment_Date, Amount) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($payment_query);
    $stmt->bind_param("isd", $payroll_id, $payment_date, $net_salary);
    
    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Payroll processed and marked as paid successfully',
            'data' => [
                'Payroll_ID' => $payroll_id,
                'Emp_ID' => $emp_id,
                'Payroll_Date' => $payroll_date,
                'Gross_salary' => $gross_salary,
                'Tax' => (float)$tax,
                'Insurance' => (float)$insurance,
                'Net_salary' => $net_salary
            ]
        ];
    } else {
        $response = ['success' => false, 'message' => 'Failed to record payment: ' . $conn->error];
    }
} else {
    $response = ['success' => false, 'message' => 'Failed to process payroll: ' . $conn->error];
}

echo json_encode($response);
$conn->close();
?>