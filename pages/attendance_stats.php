<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_role('admin');
require_once __DIR__ . '/../personnel-management/db/connect.php';

$month = date('Y-m');
$sql = "SELECT e.name, 
    SUM(a.status='正常') AS normal_days,
    SUM(a.status<>'正常') AS abnormal_days
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    WHERE DATE_FORMAT(a.date, '%Y-%m') = ?
    GROUP BY a.employee_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $month);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>本月考勤统计</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background: #f8f8f8;}
        .container { width:500px;margin:40px auto;background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 10px #eee;}
        h2 { text-align:center;}
        table { width:100%; border-collapse:collapse; margin-top:16px;}
        th, td { padding:8px 10px; border:1px solid #eee;}
        .links { margin-top: 22px; text-align: center; }
        .links a { margin: 0 10px; color: #234599; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h2><?= htmlspecialchars($month) ?> 员工考勤统计</h2>
    <table>
        <tr><th>员工姓名</th><th>正常天数</th><th>异常/补卡天数</th></tr>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= intval($row['normal_days']) ?></td>
            <td><?= intval($row['abnormal_days']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <div class="links">
        <a href="attendance_list.php">返回考勤记录</a>
        |
        <a href="../personnel-management/index.php">返回首页</a>
    </div>
</div>
</body>
</html>