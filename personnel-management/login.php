<?php
session_start();
require_once __DIR__ . '/db/connect.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username']);
    $p = trim($_POST['password']);
    $stmt = $conn->prepare("SELECT password, role FROM users WHERE username=?");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    $stmt->bind_result($db_password, $role);
    if ($stmt->fetch() && $db_password === $p) { // 实际项目建议用 password_verify
        $_SESSION['user'] = $u;
        // 统一角色为小写且无空格，防止判断出错
        $_SESSION['role'] = strtolower(trim($role));
        header("Location: index.php");
        exit;
    } else {
        $message = "用户名或密码错误";
    }
    $stmt->close();
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