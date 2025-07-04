<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_once __DIR__ . '/../personnel-management/db/connect.php';

$login_user = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? 'user';

// 获取员工ID
$stmt = $conn->prepare("SELECT id FROM employees WHERE username=? LIMIT 1");
$stmt->bind_param("s", $login_user);
$stmt->execute();
$stmt->bind_result($employee_id);
$stmt->fetch();
$stmt->close();

// 管理员可查全部，员工只查自己
if ($role === 'admin') {
    $sql = "SELECT l.*, e.name FROM leave_requests l JOIN employees e ON l.employee_id = e.id ORDER BY l.created_at DESC";
    $res = $conn->query($sql);
} else {
    $sql = "SELECT l.*, e.name FROM leave_requests l JOIN employees e ON l.employee_id = e.id WHERE l.employee_id=? ORDER BY l.created_at DESC";
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
    <title>请假记录</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif;}
        .container { width: 900px; margin: 40px auto; background: #fff; border-radius: 6px; box-shadow: 0 2px 10px #eee; padding: 30px;}
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px;}
        th, td { padding: 8px 10px; border: 1px solid #ddd;}
        .status-pending { color: #c90; }
        .status-approved { color: #26a042; }
        .status-rejected { color: #c00; }
    </style>
</head>
<body>
<div class="container">
    <h2>请假记录</h2>
    <table>
        <tr>
            <th>员工姓名</th>
            <th>开始日期</th>
            <th>结束日期</th>
            <th>天数</th>
            <th>事由</th>
            <th>状态</th>
            <th>管理员回复</th>
            <th>申请时间</th>
            <?php if ($role === 'admin'): ?><th>操作</th><?php endif; ?>
        </tr>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['start_date']) ?></td>
            <td><?= htmlspecialchars($row['end_date']) ?></td>
            <td><?= (strtotime($row['end_date']) - strtotime($row['start_date']))/86400 + 1 ?></td>
            <td><?= htmlspecialchars($row['reason']) ?></td>
            <td class="<?php
                if ($row['status'] == '待审核') echo 'status-pending';
                elseif ($row['status'] == '已批准') echo 'status-approved';
                else echo 'status-rejected';
            ?>"><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['reply'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <?php if ($role === 'admin'): ?>
                <td>
                    <?php if ($row['status'] == '待审核'): ?>
                        <a href="leave_review.php?id=<?= $row['id'] ?>">审核</a>
                    <?php else: ?>
                        --
                    <?php endif; ?>
                </td>
            <?php endif; ?>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <?php if ($role !== 'admin'): ?>
    <a href="leave_apply.php">我要请假</a>
    <?php endif; ?>
    <a href="employee_list.php">返回员工列表</a>
</div>
</body>
</html>