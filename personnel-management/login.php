<?php
session_start();
require_once __DIR__ . '/db/connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = '用户名和密码不能为空';
    } else {
        // 查询用户
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username=? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        // 用password_verify比对密码（数据库里是hash），初始密码123456也能登录
        if ($user && password_verify($password, $user['password'])) {
            // 查询员工表，判断该账号是否为离职状态
            $stmt2 = $conn->prepare("SELECT status FROM employees WHERE username=? LIMIT 1");
            $stmt2->bind_param('s', $username);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            $emp = $res2->fetch_assoc();
            $stmt2->close();

            if ($emp && $emp['status'] !== '在职') {
                $message = "该账号已离职，无法登录！";
            } else {
                // 登录成功
                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: index.php');
                exit;
            }
        } else {
            $message = '用户名或密码错误';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>登录 - 人事管理系统</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background: #f8f8f8;}
        .container { width: 350px; margin: 80px auto; background: #fff; border-radius: 6px; box-shadow: 0 2px 10px #eee; padding: 30px;}
        h2 { text-align: center; }
        label { display: block; margin-top: 12px;}
        input[type="text"], input[type="password"] { width: 100%; padding: 8px;}
        .btn { margin-top: 18px; padding: 8px 20px; width:100%; }
        .msg { color:red; text-align:center; margin-bottom:12px;}
    </style>
</head>
<body>
<div class="container">
    <h2>人事管理系统登录</h2>
    <?php if ($message): ?>
        <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post">
        <label>用户名：<input type="text" name="username" autocomplete="username" required></label>
        <label>密码：<input type="password" name="password" autocomplete="current-password" required></label>
        <button class="btn" type="submit">登录</button>
    </form>
</div>
</body>
</html>