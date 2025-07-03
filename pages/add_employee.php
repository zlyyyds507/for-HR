<?php
require_once __DIR__ . '/../auth.php';
require_login();
require_role('admin');
?>

<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../personnel-management/login.php");
    exit;
}
?>

<?php
require_once __DIR__ . '/../personnel-management/db/connect.php';

$name = $position = $department = $hire_date = $salary = "";
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据并简单过滤
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $department = trim($_POST['department']);
    $hire_date = $_POST['hire_date'];
    $salary = $_POST['salary'];

    // 简单校验
    if ($name === "" || $hire_date === "" || $salary === "") {
        $message = "姓名、入职日期和薪资为必填项！";
    } else {
        // 插入数据库
        $stmt = $conn->prepare("INSERT INTO employees (name, position, department, hire_date, salary) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssd", $name, $position, $department, $hire_date, $salary);
        if ($stmt->execute()) {
            header("Location: employee_list.php");
            exit;
        } else {
            $message = "添加失败：" . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>添加新员工</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; }
        form { width: 400px; margin: 40px auto; }
        label { display: block; margin-top: 12px; }
        input[type="text"], input[type="date"], input[type="number"] { width: 100%; padding: 8px; }
        .btn { margin-top: 16px; padding: 8px 20px; }
        .msg { color: red; margin-bottom: 10px; }
        a { display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>
    <a href="employee_list.php">&lt; 返回员工列表</a>
    <h2>添加新员工</h2>
    <?php if ($message): ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post">
        <label>姓名：<input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required></label>
        <label>职位：<input type="text" name="position" value="<?php echo htmlspecialchars($position); ?>"></label>
        <label>部门：<input type="text" name="department" value="<?php echo htmlspecialchars($department); ?>"></label>
        <label>入职日期：<input type="date" name="hire_date" value="<?php echo htmlspecialchars($hire_date); ?>" required></label>
        <label>薪资：<input type="number" step="0.01" name="salary" value="<?php echo htmlspecialchars($salary); ?>" required></label>
        <button class="btn" type="submit">添加</button>
    </form>
</body>
</html>