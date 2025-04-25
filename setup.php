<?php
require_once 'db_connect.php';

// Clear any previous output
ob_clean();

// Create tables
$conn->query("CREATE TABLE IF NOT EXISTS Employee (
    Emp_ID INT PRIMARY KEY,
    First_name VARCHAR(50) NOT NULL,
    Last_name VARCHAR(50) NOT NULL,
    DOB DATE NOT NULL,
    Hire_date DATE NOT NULL,
    Department VARCHAR(50) NOT NULL,
    Position VARCHAR(50) NOT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS Salary (
    Emp_ID INT PRIMARY KEY,
    Basic_salary DECIMAL(10,2) NOT NULL,
    Allowance DECIMAL(10,2) NOT NULL,
    Bonus DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (Emp_ID) REFERENCES Employee(Emp_ID)
)");

$conn->query("CREATE TABLE IF NOT EXISTS Deductions (
    Emp_ID INT PRIMARY KEY,
    Tax DECIMAL(10,2) DEFAULT 0,
    Insurance DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (Emp_ID) REFERENCES Employee(Emp_ID)
)");

$conn->query("CREATE TABLE IF NOT EXISTS Payroll (
    Payroll_ID INT AUTO_INCREMENT PRIMARY KEY,
    Emp_ID INT NOT NULL,
    Payroll_Date DATE NOT NULL,
    Total_salary DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (Emp_ID) REFERENCES Employee(Emp_ID)
)");

$conn->query("CREATE TABLE IF NOT EXISTS Payment (
    Payment_ID INT AUTO_INCREMENT PRIMARY KEY,
    Payroll_ID INT NOT NULL,
    Payment_Date DATE NOT NULL,
    Amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (Payroll_ID) REFERENCES Payroll(Payroll_ID)
)");

// Insert demo data
$conn->query("INSERT IGNORE INTO Employee (Emp_ID, First_name, Last_name, DOB, Hire_date, Department, Position) VALUES
    (1, 'John', 'Doe', '1990-01-15', '2020-01-01', 'IT', 'Developer'),
    (2, 'Jane', 'Smith', '1992-03-20', '2021-02-01', 'HR', 'Manager'),
    (3, 'Alice', 'Johnson', '1988-07-10', '2019-03-01', 'Finance', 'Accountant'),
    (4, 'Bob', 'Williams', '1995-05-05', '2022-04-01', 'IT', 'Analyst')
");

$conn->query("INSERT IGNORE INTO Salary (Emp_ID, Basic_salary, Allowance, Bonus) VALUES
    (1, 5000.00, 1000.00, 500.00),
    (2, 6000.00, 1200.00, 0.00),
    (3, 5500.00, 1100.00, 300.00),
    (4, 4500.00, 800.00, 200.00)
");

$conn->query("INSERT IGNORE INTO Deductions (Emp_ID, Tax, Insurance) VALUES
    (1, 400.00, 200.00),
    (2, 500.00, 250.00),
    (3, 450.00, 220.00),
    (4, 350.00, 180.00)
");

$conn->query("INSERT IGNORE INTO Payroll (Emp_ID, Payroll_Date, Total_salary) VALUES
    (1, '2025-03-31', 6500.00),
    (2, '2025-03-31', 7200.00),
    (3, '2025-03-31', 6900.00),
    (4, '2025-03-31', 5500.00)
");

$conn->query("INSERT IGNORE INTO Payment (Payroll_ID, Payment_Date, Amount) VALUES
    (1, '2025-04-01', 6500.00),
    (2, '2025-04-01', 7200.00)
");

echo json_encode(['success' => true, 'message' => 'Database setup completed successfully']);
$conn->close();
?>