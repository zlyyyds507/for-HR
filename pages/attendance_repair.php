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

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $type = $_POST['type'] ?? '';
    $repair_time = $_POST['repair_time'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    if (!$date || !$type || !$repair_time || !$reason) {
        $message = "请填写完整信息！";
    } else {
        $stmt = $conn->prepare("INSERT INTO attendance_repair (employee_id, date, type, repair_time, reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $employee_id, $date, $type, $repair_time, $reason);
        if ($stmt->execute()) {
            $message = "补卡申请已提交，等待管理员审核。";
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
    <title>补卡申请</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background:#f8f8f8;}
        .container { width:400px;margin:50px auto;background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 10px #eee;}
        h2 { text-align:center;}
        .msg { color:green;text-align:center;margin-bottom:12px;}
        label { display:block;margin-top:14px;}
        input, select, textarea { width:100%;padding:8px;}
        .btn { width:100%;padding:12px 0;margin:24px 0 0 0;font-size:17px;background:#2492ff;color:#fff;border:none;border-radius:6px;}
        .links { margin-top: 22px; text-align: center; }
        .links a { margin: 0 10px; color: #234599; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>补卡申请</h2>
    <?php if ($message): ?><div class="msg"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
        <label>补卡日期：<input type="date" name="date" required></label>
        <label>类型：
            <select name="type" required>
                <option value="">请选择</option>
                <option value="check_in">上班补卡</option>
                <option value="check_out">下班补卡</option>
            </select>
        </label>
        <label>补卡时间：<input type="datetime-local" name="repair_time" required></label>
        <label>补卡原因：<textarea name="reason" required></textarea></label>
        <button class="btn" type="submit">提交申请</button>
    </form>
    <div class="links">
        <a href="attendance_repair_list.php">查看我的补卡记录</a>
        |
        <a href="../personnel-management/index.php">返回首页</a>
    </div>
</div>
</body>
</html>