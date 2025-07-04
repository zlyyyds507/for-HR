<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_once __DIR__ . '/../personnel-management/db/connect.php';

$login_user = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? 'user';

if ($role === 'admin') {
    $sql = "SELECT a.*, e.name FROM attendance a JOIN employees e ON a.employee_id = e.id ORDER BY a.date DESC";
    $res = $conn->query($sql);
} else {
    $stmt = $conn->prepare("SELECT id FROM employees WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $login_user);
    $stmt->execute();
    $stmt->bind_result($employee_id);
    $stmt->fetch();
    $stmt->close();
    $sql = "SELECT a.*, e.name FROM attendance a JOIN employees e ON a.employee_id = e.id WHERE a.employee_id=? ORDER BY a.date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $res = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>考勤记录</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; }
        .container { width:900px;margin:40px auto;background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 10px #eee;}
        h2 { text-align:center; }
        table { width:100%; border-collapse:collapse; margin-top:16px;}
        th, td { padding:8px 10px; border:1px solid #eee;}
        .status-normal { color:#26a042; }
        .status-abnormal { color:#d00;}
        .status-repair { color:#f60;}
    </style>
</head>
<body>
<div class="container">
    <h2>考勤记录</h2>
    <table>
        <tr>
            <th>员工</th>
            <th>日期</th>
            <th>上班打卡</th>
            <th>下班打卡</th>
            <th>状态</th>
            <th>备注</th>
        </tr>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['check_in']) ?></td>
            <td><?= htmlspecialchars($row['check_out']) ?></td>
            <td class="<?php
                if ($row['status']=='正常') echo 'status-normal';
                elseif ($row['status']=='补卡中') echo 'status-repair';
                else echo 'status-abnormal';
            ?>"><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['remark']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <a href="attendance_checkin.php">打卡</a> |
    <a href="attendance_repair.php">补卡申请</a>
    <div class="links" style="text-align:center;margin-top:22px;">
        <a href="attendance_checkin.php">打卡</a>
        |
        <a href="attendance_repair.php">补卡申请</a>
        |
        <a href="../personnel-management/index.php">返回首页</a>
    </div>
</div>
</body>
</html>