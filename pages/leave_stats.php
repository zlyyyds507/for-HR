<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_role('admin');
require_once __DIR__ . '/../personnel-management/db/connect.php';

// 统计每位员工本年已批准请假的天数
$year = date('Y');
$sql = "SELECT e.name, SUM(DATEDIFF(l.end_date, l.start_date) + 1) AS days
        FROM leave_requests l
        JOIN employees e ON l.employee_id = e.id
        WHERE l.status='已批准' AND YEAR(l.start_date)=?
        GROUP BY l.employee_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $year);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>请假统计</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif;}
        .container { width: 500px; margin: 40px auto; background: #fff; border-radius: 6px; box-shadow: 0 2px 10px #eee; padding: 30px;}
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px;}
        th, td { padding: 8px 10px; border: 1px solid #ddd;}
    </style>
</head>
<body>
<div class="container">
    <h2><?= $year ?>年员工请假天数统计</h2>
    <table>
        <tr><th>员工姓名</th><th>请假天数</th></tr>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= intval($row['days']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <a href="leave_list.php">返回请假记录</a>
</div>
</body>
</html>