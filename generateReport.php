<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Clear any previous output
ob_clean();

// Get report parameters
$report_type = $_GET['type'] ?? 'monthly-payroll';
$month = $_GET['month'] ?? date('Y-m');

// Validate report type
$valid_types = ['monthly-payroll', 'employee-wise', 'department-wise'];
if (!in_array($report_type, $valid_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid report type']);
    exit;
}

// Generate report
switch ($report_type) {
    case 'monthly-payroll':
        $report = generateMonthlyPayrollReport($conn, $month);
        break;
    case 'employee-wise':
        $report = generateEmployeeWiseReport($conn);
        break;
    case 'department-wise':
        $report = generateDepartmentWiseReport($conn);
        break;
}

echo json_encode([
    'success' => true,
    'report_type' => $report_type,
    'month' => $month,
    'data' => $report
], JSON_NUMERIC_CHECK);

$conn->close();

// Report Generation Functions
function generateMonthlyPayrollReport($conn, $month) {
    $query = "SELECT p.Payroll_ID, e.First_name, e.Last_name, p.Payroll_Date, 
                     p.Total_salary, 
                     COALESCE(d.Tax, 0) AS Tax, 
                     COALESCE(d.Insurance, 0) AS Insurance,
                     (COALESCE(d.Tax, 0) + COALESCE(d.Insurance, 0)) AS Total_deductions,
                     (p.Total_salary - (COALESCE(d.Tax, 0) + COALESCE(d.Insurance, 0))) AS Net_salary
              FROM Payroll p
              JOIN Employee e ON p.Emp_ID = e.Emp_ID
              LEFT JOIN Deductions d ON p.Emp_ID = d.Emp_ID
              WHERE DATE_FORMAT(p.Payroll_Date, '%Y-%m') = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $month);
    $stmt->execute();
    $result = $stmt->get_result();

    $report = [
        'month' => $month,
        'payrolls' => [],
        'totals' => ['gross' => 0, 'deductions' => 0, 'net' => 0]
    ];

    while ($row = $result->fetch_assoc()) {
        $report['payrolls'][] = $row;
        $report['totals']['gross'] += $row['Total_salary'];
        $report['totals']['deductions'] += $row['Total_deductions'];
        $report['totals']['net'] += $row['Net_salary'];
    }

    return $report;
}

function generateEmployeeWiseReport($conn) {
    $query = "SELECT e.Emp_ID, e.First_name, e.Last_name, e.Department,
                     COALESCE(s.Basic_salary, 0) AS Basic_salary,
                     COALESCE(s.Allowance, 0) AS Allowance,
                     COALESCE(s.Bonus, 0) AS Bonus,
                     (COALESCE(s.Basic_salary, 0) + COALESCE(s.Allowance, 0) + COALESCE(s.Bonus, 0)) AS Gross_salary,
                     COALESCE(d.Tax, 0) AS Tax,
                     COALESCE(d.Insurance, 0) AS Insurance,
                     (COALESCE(d.Tax, 0) + COALESCE(d.Insurance, 0)) AS Total_deductions,
                     (COALESCE(s.Basic_salary, 0) + COALESCE(s.Allowance, 0) + COALESCE(s.Bonus, 0) - 
                      COALESCE(d.Tax, 0) - COALESCE(d.Insurance, 0)) AS Net_salary
              FROM Employee e
              LEFT JOIN Salary s ON e.Emp_ID = s.Emp_ID
              LEFT JOIN Deductions d ON e.Emp_ID = d.Emp_ID";
    
    $result = $conn->query($query);
    if (!$result) {
        return ['success' => false, 'message' => 'Query failed: ' . $conn->error];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

function generateDepartmentWiseReport($conn) {
    $query = "SELECT e.Department,
                     COUNT(e.Emp_ID) AS Employee_count,
                     COALESCE(SUM(s.Basic_salary), 0) AS Total_basic,
                     COALESCE(SUM(s.Allowance), 0) AS Total_allowance,
                     COALESCE(SUM(s.Bonus), 0) AS Total_bonus,
                     COALESCE(SUM(s.Basic_salary + s.Allowance + s.Bonus), 0) AS Total_gross,
                     COALESCE(SUM(d.Tax + d.Insurance), 0) AS Total_deductions,
                     COALESCE(SUM(s.Basic_salary + s.Allowance + s.Bonus - (d.Tax + d.Insurance)), 0) AS Total_net
              FROM Employee e
              LEFT JOIN Salary s ON e.Emp_ID = s.Emp_ID
              LEFT JOIN Deductions d ON e.Emp_ID = d.Emp_ID
              GROUP BY e.Department";
    
    $result = $conn->query($query);
    if (!$result) {
        return ['success' => false, 'message' => 'Query failed: ' . $conn->error];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>