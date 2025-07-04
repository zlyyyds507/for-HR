<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_once __DIR__ . '/../personnel-management/db/connect.php';

$login_user = $_SESSION['user'] ?? '';
// 获取员工ID
$stmt = $conn->prepare("SELECT id FROM employees WHERE username=? LIMIT 1");
$stmt->bind_param("s", $login_user);
$stmt->execute();
$stmt->bind_result($employee_id);
$stmt->fetch();
$stmt->close();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    if (!$start_date || !$end_date || !$reason) {
        $message = "请填写完整信息！";
    } else {
        $stmt = $conn->prepare("INSERT INTO leave_requests (employee_id, start_date, end_date, reason) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $employee_id, $start_date, $end_date, $reason);
        if ($stmt->execute()) {
            $message = "请假申请已提交，等待管理员审核。";
        } else {
            $message = "提交失败：" . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>申请请假</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background: #f8f8f8;}
        .container { width: 400px; margin: 40px auto; background: #fff; border-radius: 6px; box-shadow: 0 2px 10px #eee; padding: 30px;}
        h2 { text-align: center; }
        label { display: block; margin-top: 12px;}
        input, textarea { width: 100%; padding: 8px;}
        .btn { margin-top: 18px; padding: 8px 20px; width:100%; }
        .msg { color:red; text-align:center; margin-bottom:12px;}
    </style>
</head>
<body>
<div class="container">
    <h2>申请请假</h2>
    <?php if ($message): ?><div class="msg"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
        <label>开始日期：<input type="date" name="start_date" required></label>
        <label>结束日期：<input type="date" name="end_date" required></label>
        <label>请假事由：<textarea name="reason" required rows="4"></textarea></label>
        <button class="btn" type="submit">提交申请</button>
    </form>
    <a href="leave_list.php">查看我的请假记录</a>
</div>
</body>
</html>