<?php
require_once __DIR__ . '/auth.php';
require_login();
require_role('admin');
require_once __DIR__ . '/db/connect.php';

// 获取所有用户
$users = [];
$res = $conn->query("SELECT id, username, role FROM users ORDER BY id ASC");
while($row = $res->fetch_assoc()) $users[] = $row;

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $newpass = trim($_POST['new_password']);
    $confirm = trim($_POST['confirm_password']);

    if ($newpass === '' || $newpass !== $confirm) {
        $message = "新密码不能为空且两次输入必须一致";
    } else {
        $hash = password_hash($newpass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hash, $user_id);
        if ($stmt->execute()) {
            $message = "重置成功";
        } else {
            $message = "重置失败：" . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>管理员重置用户密码</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background: #f8f8f8; }
        .container { width: 420px; margin: 40px auto; background: #fff; border-radius: 6px; box-shadow: 0 2px 10px #eee; padding: 30px; }
        .msg { color: green; margin-bottom: 16px; }
        .err { color: red; margin-bottom: 16px; }
        select, input[type="password"] { width: 100%; padding: 8px; margin-bottom: 12px; }
        button { padding: 8px 20px; }
        label { display:block; margin-bottom: 6px; }
        .back { margin-top: 16px; display: block;}
    </style>
</head>
<body>
<div class="container">
    <h2>管理员重置用户密码</h2>
    <?php if ($message): ?><div class="<?= strpos($message,'成功')!==false?'msg':'err' ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
        <label>选择用户：</label>
        <select name="user_id" required>
            <option value="">请选择</option>
            <?php foreach($users as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?>（<?= htmlspecialchars($u['role']) ?>）</option>
            <?php endforeach; ?>
        </select>
        <label>新密码：</label>
        <input type="password" name="new_password" required>
        <label>确认新密码：</label>
        <input type="password" name="confirm_password" required>
        <button type="submit">重置密码</button>
    </form>
    <a class="back" href="index.php">&lt; 返回首页</a>
</div>
</body>
</html>