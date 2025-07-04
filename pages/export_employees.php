<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_once __DIR__ . '/../personnel-management/db/connect.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=employees.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['姓名', '部门', '职位', '联系方式', '入职状态']);

$res = $conn->query("SELECT name, department, position, contact, status FROM employees");
while($row = $res->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit;
?>