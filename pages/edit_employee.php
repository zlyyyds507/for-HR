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

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "参数错误！";
    exit;
}

$message = "";

// 删除操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM employees WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: employee_list.php");
        exit;
    } else {
        $message = "删除失败：" . $stmt->error;
    }
    $stmt->close();
}

// 修改操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $department = trim($_POST['department']);
    $hire_date = $_POST['hire_date'];
    $salary = $_POST['salary'];

    if ($name === "" || $hire_date === "" || $salary === "") {
        $message = "姓名、入职日期和薪资为必填项！";
    } else {
        $stmt = $conn->prepare("UPDATE employees SET name=?, position=?, department=?, hire_date=?, salary=? WHERE id=?");
        $stmt->bind_param("ssssdi", $name, $position, $department, $hire_date, $salary, $id);
        if ($stmt->execute()) {
            header("Location: employee_list.php");
            exit;
        } else {
            $message = "修改失败：" . $stmt->error;
        }
        $stmt->close();
    }
}

// 获取原始数据
$stmt = $conn->prepare("SELECT * FROM employees WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

if (!$employee) {
    echo "未找到该员工！";
    exit;
}
$name = $employee['name'];
$position = $employee['position'];
$department = $employee['department'];
$hire_date = $employee['hire_date'];
$salary = $employee['salary'];
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>编辑员工信息</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; }
        form { width: 400px; margin: 40px auto; }
        label { display: block; margin-top: 12px; }
        input[type="text"], input[type="date"], input[type="number"] { width: 100%; padding: 8px; }
        .btn { margin-top: 16px; padding: 8px 20px; }
        .msg { color: red; margin-bottom: 10px; }
        .flex-row { display: flex; justify-content: space-between; }
        .flex-row button { width: 48%; }
        a { display: inline-block; margin-bottom: 20px; }
    </style>
    <script>
        function confirmDelete() {
            return confirm("确定要删除此员工吗？此操作不可恢复！");
        }
    </script>
</head>
<body>
    <a href="employee_list.php">&lt; 返回员工列表</a>
    <h2>编辑员工信息</h2>
    <?php if ($message): ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post">
        <label>姓名：<input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required></label>
        <label>职位：<input type="text" name="position" value="<?php echo htmlspecialchars($position); ?>"></label>
        <label>部门：<input type="text" name="department" value="<?php echo htmlspecialchars($department); ?>"></label>
        <label>入职日期：<input type="date" name="hire_date" value="<?php echo htmlspecialchars($hire_date); ?>" required></label>
        <label>薪资：<input type="number" step="0.01" name="salary" value="<?php echo htmlspecialchars($salary); ?>" required></label>
        <div class="flex-row">
            <button class="btn" type="submit" name="save">保存修改</button>
            <button class="btn" type="submit" name="delete" style="background:#c00; color:#fff;" onclick="return confirmDelete();">删除员工</button>
        </div>
    </form>
</body>
</html>