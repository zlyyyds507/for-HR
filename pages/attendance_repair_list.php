<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_once __DIR__ . '/../personnel-management/db/connect.php';

$login_user = $_SESSION['user'] ?? '';
$stmt = $conn->prepare("SELECT id FROM employees WHERE username=? LIMIT 1");
$stmt->bind_param("s", $login_user);
$stmt->execute();
$stmt->bind_result($employee_id);
$stmt->fetch();
$stmt->close();

$sql = "SELECT * FROM attendance_repair WHERE employee_id=? ORDER BY date DESC, created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>我的补卡记录</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background:#f8f8f8;}
        .container { width:700px;margin:40px auto;background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 10px #eee;}
        h2 { text-align:center;}
        table { width:100%; border-collapse:collapse; margin-top:16px;}
        th, td { padding:8px 10px; border:1px solid #eee;}
        .status-pending { color: #c90;}
        .status-passed { color: #26a042;}
        .status-reject { color: #c00;}
        .links { margin-top: 22px; text-align: center; }
        .links a { margin: 0 10px; color: #234599; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>我的补卡记录</h2>
    <table>
        <tr>
            <th>日期</th>
            <th>类型</th>
            <th>补卡时间</th>
            <th>原因</th>
            <th>状态</th>
            <th>审核回复</th>
            <th>申请时间</th>
        </tr>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= $row['type']=='check_in'?'上班补卡':'下班补卡' ?></td>
            <td><?= htmlspecialchars($row['repair_time']) ?></td>
            <td><?= htmlspecialchars($row['reason']) ?></td>
            <td class="<?php
                if ($row['status']=='待审核') echo 'status-pending';
                elseif ($row['status']=='已通过') echo 'status-passed';
                else echo 'status-reject';
            ?>"><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['reply']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <div class="links">
        <a href="attendance_repair.php">返回补卡申请</a>
        |
        <a href="../personnel-management/index.php">返回首页</a>
    </div>
</div>
</body>
</html>