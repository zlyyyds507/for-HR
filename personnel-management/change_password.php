<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/db/connect.php';

$message = '';
$username = $_SESSION['user'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldpass = $_POST['old_password'] ?? '';
    $newpass = $_POST['new_password'] ?? '';
    $confirmpass = $_POST['confirm_password'] ?? '';

    if ($newpass === '' || $newpass !== $confirmpass) {
        $message = '新密码不能为空且两次输入必须一致';
    } else {
        // 查原密码
        $stmt = $conn->prepare("SELECT password FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($hash);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($oldpass, $hash)) {
            $message = '原密码错误';
        } else {
            $new_hash = password_hash($newpass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE username=?");
            $stmt->bind_param("ss", $new_hash, $username);
            if ($stmt->execute()) {
                $message = '密码修改成功！';
            } else {
                $message = '修改失败，请重试';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>修改密码</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background: #f8f8f8;}
        .container { width:350px; margin:40px auto; background:#fff; border-radius:6px; box-shadow:0 2px 10px #eee; padding:30px;}
        label { display:block; margin-top:10px;}
        input[type="password"] { width:100%; padding:8px;}
        button { margin-top:18px; width:100%; padding:10px;}
        .msg { margin:10px 0; color:green;}
        .err { margin:10px 0; color:red;}
        a { display:block; margin-top:16px;}
    </style>
</head>
<body>
<div class="container">
    <h2>修改密码</h2>
    <form method="post">
        <label>原密码：<input type="password" name="old_password" required></label>
        <label>新密码：<input type="password" name="new_password" required></label>
        <label>确认新密码：<input type="password" name="confirm_password" required></label>
        <button type="submit">提交</button>
    </form>
    <?php if ($message): ?>
        <div class="<?= strpos($message,'成功')!==false?'msg':'err' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <a href="index.php">返回首页</a>
</div>
</body>
</html>