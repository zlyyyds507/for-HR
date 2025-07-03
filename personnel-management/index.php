<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
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
    </style>
</head>
<body>
    <h1>欢迎来到人事管理系统</h1>
    <ul>
        <li><a href="../pages/employee_list.php">员工列表</a></li>
    </ul>
    <a class="logout" href="logout.php">退出登录</a>
</body>
</html>