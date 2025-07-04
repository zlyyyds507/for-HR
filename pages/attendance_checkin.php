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

$date = date('Y-m-d');
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $now = date('Y-m-d H:i:s');
    $type = $_POST['type'] ?? '';
    // 查询今天是否已有考勤记录
    $stmt = $conn->prepare("SELECT id, check_in, check_out FROM attendance WHERE employee_id=? AND date=? LIMIT 1");
    $stmt->bind_param("is", $employee_id, $date);
    $stmt->execute();
    $stmt->bind_result($att_id, $check_in, $check_out);
    $found = $stmt->fetch();
    $stmt->close();

    if ($type === 'check_in') {
        if ($found && $check_in) {
            $message = "今天已打过上班卡";
        } else if ($found) {
            $stmt = $conn->prepare("UPDATE attendance SET check_in=? WHERE id=?");
            $stmt->bind_param("si", $now, $att_id);
            $stmt->execute();
            $stmt->close();
            $message = "上班打卡成功";
        } else {
            $stmt = $conn->prepare("INSERT INTO attendance (employee_id, date, check_in) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $employee_id, $date, $now);
            $stmt->execute();
            $stmt->close();
            $message = "上班打卡成功";
        }
    } elseif ($type === 'check_out') {
        if ($found && $check_out) {
            $message = "今天已打过下班卡";
        } else if ($found) {
            $stmt = $conn->prepare("UPDATE attendance SET check_out=? WHERE id=?");
            $stmt->bind_param("si", $now, $att_id);
            $stmt->execute();
            $stmt->close();
            $message = "下班打卡成功";
        } else {
            $stmt = $conn->prepare("INSERT INTO attendance (employee_id, date, check_out) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $employee_id, $date, $now);
            $stmt->execute();
            $stmt->close();
            $message = "下班打卡成功";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>考勤打卡</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background:#f8f8f8;}
        .container { width:400px;margin:50px auto;background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 10px #eee;}
        h2 { text-align:center;}
        .msg { color:green;text-align:center;margin-bottom:12px;}
        .btn { width:100%;padding:14px 0;margin:20px 0 0 0;font-size:18px;background:#2492ff;color:#fff;border:none;border-radius:6px;}
    </style>
</head>
<body>
<div class="container">
    <h2>考勤打卡</h2>
    <?php if ($message): ?><div class="msg"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
        <button class="btn" type="submit" name="type" value="check_in">上班打卡</button>
        <button class="btn" type="submit" name="type" value="check_out">下班打卡</button>
    </form>
    <a href="attendance_list.php">查看我的考勤记录</a>
</div>
</body>
</html>