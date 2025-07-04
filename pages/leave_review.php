<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_role('admin');
require_once __DIR__ . '/../personnel-management/db/connect.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo "参数错误";
    exit;
}

$message = "";
// 查询请假申请
$stmt = $conn->prepare("SELECT l.*, e.name FROM leave_requests l JOIN employees e ON l.employee_id = e.id WHERE l.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    echo "未找到请假申请";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $reply = trim($_POST['reply'] ?? '');
    $reviewer = $_SESSION['user'];
    if ($status !== '已批准' && $status !== '已驳回') {
        $message = "请选择审核结果";
    } else {
        $stmt = $conn->prepare("UPDATE leave_requests SET status=?, reply=?, reviewer=?, reviewed_at=NOW() WHERE id=?");
        $stmt->bind_param("sssi", $status, $reply, $reviewer, $id);
        if ($stmt->execute()) {
            header("Location:leave_list.php");
            exit;
        } else {
            $message = "审核失败：" . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>审核请假申请</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background: #f8f8f8;}
        .container { width: 400px; margin: 40px auto; background: #fff; border-radius: 6px; box-shadow: 0 2px 10px #eee; padding: 30px;}
        h2 { text-align: center; }
        label { display: block; margin-top: 12px;}
        textarea { width: 100%; padding: 8px;}
        .btn { margin-top: 18px; padding: 8px 20px; width:100%; }
        .msg { color:red; text-align:center; margin-bottom:12px;}
    </style>
</head>
<body>
<div class="container">
    <h2>审核请假申请</h2>
    <table>
        <tr><th>员工</th><td><?= htmlspecialchars($row['name']) ?></td></tr>
        <tr><th>起止</th><td><?= htmlspecialchars($row['start_date']) ?> ~ <?= htmlspecialchars($row['end_date']) ?></td></tr>
        <tr><th>事由</th><td><?= htmlspecialchars($row['reason']) ?></td></tr>
        <tr><th>申请时间</th><td><?= htmlspecialchars($row['created_at']) ?></td></tr>
    </table>
    <?php if ($message): ?><div class="msg"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
        <label>审核结果：
            <select name="status" required>
                <option value="">请选择</option>
                <option value="已批准">批准</option>
                <option value="已驳回">驳回</option>
            </select>
        </label>
        <label>审核说明（可选）：<textarea name="reply" rows="3"></textarea></label>
        <button class="btn" type="submit">提交审核</button>
    </form>
    <a href="leave_list.php">返回请假记录</a>
</div>
</body>
</html>