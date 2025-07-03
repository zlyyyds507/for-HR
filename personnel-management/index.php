<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];
$role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : 'user';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>人事管理系统-首页</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; text-align: center; margin-top: 50px; }
        ul { list-style: none; padding: 0; }
        li { margin: 20px 0; }
        a { font-size: 20px; text-decoration: none; color: #3366cc; }
        a:hover { text-decoration: underline; color: #003366; }
        .logout { color: #c00; font-size: 16px; }
        .userinfo { margin-bottom: 30px; color: #333; }
        .quick-links { margin: 0 auto 30px auto; display: inline-block; text-align: left; }
        .quick-links li { margin: 12px 0; }
    </style>
</head>
<body>
    <div class="userinfo">
        欢迎，<?php echo htmlspecialchars($user); ?>（<?php echo $role === 'admin' ? '管理员' : '普通员工'; ?>）
    </div>
    <ul class="quick-links">
        <li><a href="../pages/employee_list.php">员工列表</a></li>
        <?php if ($role === 'admin'): ?>

        <?php endif; ?>
    </ul>
    <a class="logout" href="logout.php">退出登录</a>
</body>
</html>