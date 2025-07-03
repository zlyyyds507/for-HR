<?php
session_start();
$users = [
    'admin' => '123456', // 用户名 => 密码（实际项目建议加密）
];
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username']);
    $p = trim($_POST['password']);
    if (isset($users[$u]) && $users[$u] === $p) {
        $_SESSION['user'] = $u;
        header("Location: ../pages/employee_list.php");
        exit;
    } else {
        $message = "用户名或密码错误";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>登录</title>
</head>
<body>
    <h2>登录人事管理系统</h2>
    <?php if($message) echo "<div style='color:red;'>$message</div>"; ?>
    <form method="post">
        用户名：<input type="text" name="username" required><br>
        密码：<input type="password" name="password" required><br>
        <button type="submit">登录</button>
    </form>
</body>
</html>