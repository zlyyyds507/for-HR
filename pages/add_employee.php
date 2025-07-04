<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_role('admin');
require_once __DIR__ . '/../personnel-management/db/connect.php';

$name = $position = $department = $hire_date = $salary = $username = "";
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $position = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $hire_date = trim($_POST['hire_date']);
    $salary = trim($_POST['salary']);
    $username = trim($_POST['username']);

    if ($name === "" || $hire_date === "" || $salary === "" || $username === "") {
        $message = "姓名、用户名、入职日期和薪资为必填项！";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $message = "该用户名已存在，请更换其他用户名。";
        } else {
            // 关键：插入时明确写status
            $emp_stmt = $conn->prepare("INSERT INTO employees (name, username, position, department, hire_date, salary, status) VALUES (?, ?, ?, ?, ?, ?, '在职')");
            $emp_stmt->bind_param("sssssd", $name, $username, $position, $department, $hire_date, $salary);
            $res1 = $emp_stmt->execute();

            $default_pwd = password_hash("123456", PASSWORD_DEFAULT);
            $role = 'user';
            $user_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $user_stmt->bind_param("sss", $username, $default_pwd, $role);
            $res2 = $user_stmt->execute();

            if ($res1 && $res2) {
                header("Location: employee_list.php");
                exit;
            } else {
                $message = "添加失败：" . $emp_stmt->error . " | " . $user_stmt->error;
            }
            $emp_stmt->close();
            $user_stmt->close();
        }
        $check->close();
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
        <label>用户名：<input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required placeholder="建议用拼音/工号等唯一值"></label>
        <label>职位：<input type="text" name="position" value="<?php echo htmlspecialchars($position); ?>"></label>
        <label>部门：<input type="text" name="department" value="<?php echo htmlspecialchars($department); ?>"></label>
        <label>入职日期：<input type="date" name="hire_date" value="<?php echo htmlspecialchars($hire_date); ?>" required></label>
        <label>薪资：<input type="number" step="0.01" name="salary" value="<?php echo htmlspecialchars($salary); ?>" required></label>
        <div style="font-size:12px;color:#888;">初始密码为：123456，添加后可在“用户管理”中重置。</div>
        <button class="btn" type="submit">添加</button>
    </form>
</body>
</html>