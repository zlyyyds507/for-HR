<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();

// 数据库连接
$servername = "localhost";
$username = "gym";      // 根据你的实际数据库用户名
$password = "314159";          // 根据你的实际数据库密码
$dbname = "人事管理系统数据库"; // 数据库名，注意与phpmyadmin一致

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 查询员工数据
$sql = "SELECT id, name, position, department FROM employees";
$result = $conn->query($sql);

$role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : 'user';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>员工列表</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; text-align: center; margin-top: 50px; }
        table { margin: 0 auto; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px 16px; }
        th { background: #f2f2f2; }
        a { color: #3366cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>员工列表</h1>
    <?php if ($role === 'admin'): ?>
        <a href="add_employee.php">添加新员工</a>
    <?php endif; ?>
    <table>
        <tr>
            <th>ID</th>
            <th>姓名</th>
            <th>职位</th>
            <th>部门</th>
            <th>操作</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['position']) ?></td>
                <td><?= htmlspecialchars($row['department']) ?></td>
                <td>
                    <?php if ($role === 'admin'): ?>
                        <a href="edit_employee.php?id=<?= $row['id'] ?>">编辑</a>
                        <a href="delete_employee.php?id=<?= $row['id'] ?>">删除</a>
                    <?php else: ?>
                        <span>无权限</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">暂无员工信息</td></tr>
        <?php endif; ?>
    </table>
    <br>
    <a href="../personnel-management/index.php">返回首页</a>
</body>
</html>
<?php
$conn->close();
?>