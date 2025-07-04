<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_once __DIR__ . '/../personnel-management/db/connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "参数错误";
    exit;
}

// 查询员工详细信息
$stmt = $conn->prepare("SELECT * FROM employees WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$emp = $res->fetch_assoc();
$stmt->close();

if (!$emp) {
    echo "未找到该员工";
    exit;
}

$role = $_SESSION['role'] ?? 'user';
$login_user = $_SESSION['user'] ?? '';
$is_self = isset($emp['username']) && ($emp['username'] === $login_user);

// 处理辞退、辞职、归档、删除
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 管理员辞退
    if (isset($_POST['fire']) && $role === 'admin' && $emp['status'] === '在职') {
        $stmt = $conn->prepare("UPDATE employees SET status='离职' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "该员工已被辞退！";
        $emp['status'] = '离职';
    }
    // 员工自助辞职
    if (isset($_POST['resign']) && $is_self && $emp['status'] === '在职') {
        $stmt = $conn->prepare("UPDATE employees SET status='离职' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "您已成功辞职！";
        $emp['status'] = '离职';
    }
    // 管理员归档
    if (isset($_POST['archive']) && $role === 'admin' && $emp['status'] === '离职') {
        $stmt = $conn->prepare("UPDATE employees SET status='已归档' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "员工已归档！";
        $emp['status'] = '已归档';
    }
    // 管理员删除（任何状态都可以删除）
    if (isset($_POST['delete_emp']) && $role === 'admin') {
        $conn->query("DELETE FROM employee_attachments WHERE employee_id=$id");
        if (!empty($emp['username'])) {
            $username = $emp['username'];
            $user_stmt = $conn->prepare("DELETE FROM users WHERE username=?");
            $user_stmt->bind_param("s", $username);
            $user_stmt->execute();
            $user_stmt->close();
        }
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
}

// 处理资料编辑
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editinfo']) && $emp['status'] !== '已归档') {
    if ($role === 'admin') {
        $contact = trim($_POST['contact']);
        $id_card = trim($_POST['id_card']);
        $education = trim($_POST['education']);
        $contract_end_date = trim($_POST['contract_end_date']);
        $stmt = $conn->prepare("UPDATE employees SET contact=?, id_card=?, education=?, contract_end_date=? WHERE id=?");
        $stmt->bind_param("ssssi", $contact, $id_card, $education, $contract_end_date, $id);
    } elseif ($is_self) {
        $contact = trim($_POST['contact']);
        $education = trim($_POST['education']);
        $stmt = $conn->prepare("UPDATE employees SET contact=?, education=? WHERE id=?");
        $stmt->bind_param("ssi", $contact, $education, $id);
    }
    if (isset($stmt) && $stmt->execute()) {
        $message = "资料修改成功！";
        $stmt_row = $conn->prepare("SELECT * FROM employees WHERE id=? LIMIT 1");
        $stmt_row->bind_param("i", $id);
        $stmt_row->execute();
        $res = $stmt_row->get_result();
        $emp = $res->fetch_assoc();
        $stmt_row->close();
    } else if (isset($stmt)) {
        $message = "资料修改失败：" . $stmt->error;
    }
    if (isset($stmt)) $stmt->close();
}

// 查询附件
$attachments = [];
$att_res = $conn->query("SELECT * FROM employee_attachments WHERE employee_id=$id ORDER BY uploaded_at DESC");
while($row = $att_res->fetch_assoc()) $attachments[] = $row;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>员工详细信息</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background: #f8f8f8; }
        .container { width: 700px; margin: 40px auto; background: #fff; border-radius: 6px; box-shadow: 0 2px 10px #eee; padding: 30px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 8px 12px; border-bottom: 1px solid #eee; text-align: left; vertical-align: top;}
        .photo { max-width:120px; border:1px solid #ddd; padding:4px; margin:4px 0; display:block;}
        .att-list a { margin-right: 16px; }
        .att-delete { color: #d00; }
        .msg { color: green; margin-bottom: 16px; }
        .back { margin-top: 16px; display: block;}
        .att-list { margin-top:6px;}
        .form-inline { display:inline; }
        .status-on { color: #26a042; }
        .status-off { color: #c00; }
        .status-archived { color: #888; }
    </style>
</head>
<body>
<div class="container">
    <h2>员工详细信息</h2>
    <a class="back" href="../personnel-management/index.php">&lt; 返回首页</a>
    <?php if ($message): ?><div class="msg"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <table>
        <tr><th>姓名</th><td><?= htmlspecialchars($emp['name']) ?></td></tr>
        <tr><th>部门</th><td><?= htmlspecialchars($emp['department']) ?></td></tr>
        <tr><th>职位</th><td><?= htmlspecialchars($emp['position']) ?></td></tr>
        <tr>
            <th>入职状态</th>
            <td class="<?= $emp['status']=='在职'?'status-on':($emp['status']=='离职'?'status-off':'status-archived') ?>">
                <?= htmlspecialchars($emp['status']) ?>
            </td>
        </tr>
        <tr><th>联系方式</th><td><?= htmlspecialchars($emp['contact'] ?? '') ?></td></tr>
        <tr><th>学历</th><td><?= htmlspecialchars($emp['education'] ?? '') ?></td></tr>
        <tr>
            <th>照片</th>
            <td>
                <?php if (!empty($emp['photo'])): ?>
                    <img class="photo" src="../uploads/<?= htmlspecialchars($emp['photo']) ?>" alt="员工照片">
                <?php else: ?>
                    <span style="color:gray;">暂无照片</span>
                <?php endif; ?>
                <!-- 本人或管理员可以上传照片，且不是已归档 -->
                <?php if ($emp['status'] !== '已归档' && ($role === 'admin' || $is_self)): ?>
                <form class="form-inline" method="post" enctype="multipart/form-data" action="upload_photo.php?id=<?= $id ?>">
                    <input type="file" name="photo" accept="image/*" required>
                    <button type="submit">上传/更换照片</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php if ($role === 'admin'): ?>
            <tr><th>身份证号</th><td><?= htmlspecialchars($emp['id_card'] ?? '') ?></td></tr>
        <?php endif; ?>
        <?php if ($role === 'admin' || $is_self): ?>
            <tr><th>合同到期日</th><td><?= htmlspecialchars($emp['contract_end_date'] ?? '') ?></td></tr>
        <?php endif; ?>
    </table>

    <!-- 操作按钮：根据状态和权限显示 -->
    <div style="margin:10px 0;">
        <?php if ($emp['status'] === '在职'): ?>
            <?php if ($role === 'admin'): ?>
                <form method="post" style="display:inline;">
                    <button type="submit" name="fire" onclick="return confirm('确定要辞退该员工吗？')">辞退员工</button>
                </form>
            <?php endif; ?>
            <?php if ($is_self && $role !== 'admin'): ?>
                <form method="post" style="display:inline;">
                    <button type="submit" name="resign" onclick="return confirm('确定要辞职吗？')">我要辞职</button>
                </form>
            <?php endif; ?>
        <?php elseif ($emp['status'] === '离职' && $role === 'admin'): ?>
            <form method="post" style="display:inline;">
                <button type="submit" name="archive" onclick="return confirm('确定要归档此离职员工吗？归档后不可恢复。')">归档</button>
            </form>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
            <form method="post" style="display:inline;">
                <button type="submit" name="delete_emp" onclick="return confirm('确定要永久删除该员工吗？此操作不可恢复！')">删除员工</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- 编辑表单：管理员或本人均可编辑，字段范围不同，且不是已归档 -->
    <?php if ($emp['status'] !== '已归档' && ($role === 'admin' || $is_self)): ?>
    <form method="post">
        <table>
            <?php if ($role === 'admin'): ?>
                <tr><th>联系方式</th><td><input type="text" name="contact" value="<?= htmlspecialchars($emp['contact'] ?? '') ?>"></td></tr>
                <tr><th>身份证号</th><td><input type="text" name="id_card" value="<?= htmlspecialchars($emp['id_card'] ?? '') ?>"></td></tr>
                <tr><th>学历</th><td><input type="text" name="education" value="<?= htmlspecialchars($emp['education'] ?? '') ?>"></td></tr>
                <tr><th>合同到期日</th><td><input type="date" name="contract_end_date" value="<?= htmlspecialchars($emp['contract_end_date'] ?? '') ?>"></td></tr>
            <?php elseif ($is_self): ?>
                <tr><th>联系方式</th><td><input type="text" name="contact" value="<?= htmlspecialchars($emp['contact'] ?? '') ?>"></td></tr>
                <tr><th>学历</th><td><input type="text" name="education" value="<?= htmlspecialchars($emp['education'] ?? '') ?>"></td></tr>
            <?php endif; ?>
            <tr>
                <td colspan="2" style="text-align:right">
                    <button type="submit" name="editinfo" value="1">保存修改</button>
                </td>
            </tr>
        </table>
    </form>
    <?php endif; ?>

    <!-- 附件显示：本人或管理员可上传/删除，其他只能看，已归档只读 -->
    <table style="margin-top:18px;">
        <tr>
            <th>附件（简历/证书等）</th>
            <td>
                <?php if ($emp['status'] !== '已归档' && ($role === 'admin' || $is_self)): ?>
                <form class="form-inline" method="post" enctype="multipart/form-data" action="upload_attachment.php?id=<?= $id ?>">
                    <input type="file" name="attachment" required>
                    <button type="submit">上传附件</button>
                </form>
                <?php endif; ?>
                <div class="att-list">
                    <?php if (count($attachments) === 0): ?>
                        <span style="color:gray;">暂无附件</span>
                    <?php endif; ?>
                    <?php foreach($attachments as $att): ?>
                        <a href="../uploads/<?= htmlspecialchars($att['file_name']) ?>" target="_blank">
                            <?= htmlspecialchars($att['original_name']) ?>
                        </a>
                        <?php if ($emp['status'] !== '已归档' && ($role === 'admin' || $is_self)): ?>
                        <a class="att-delete" href="delete_attachment.php?id=<?= $att['id'] ?>&emp_id=<?= $id ?>" onclick="return confirm('删除该附件?')">[删除]</a>
                        <?php endif; ?>
                        <br>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>