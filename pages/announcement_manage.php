<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_role('admin');
require_once __DIR__ . '/../personnel-management/db/connect.php';

// 发布公告
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title && $content) {
        $stmt = $conn->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $content);
        $stmt->execute();
        $stmt->close();
        header("Location: announcement_manage.php");
        exit;
    } else {
        $msg = "标题和内容不能为空";
    }
}

// 删除公告
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM announcements WHERE id=$del_id");
    header("Location: announcement_manage.php");
    exit;
}

// 展示最近10条公告
$result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>公告管理</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background: #f8f8f8; }
        .container { width: 700px; margin: 40px auto; background: #fff; border-radius: 6px; box-shadow: 0 2px 10px #eee; padding: 30px; }
        h1 { text-align: center; }
        form { margin-bottom: 30px; }
        label { display: block; margin-top: 12px; }
        input[type="text"], textarea { width: 100%; padding: 8px; }
        textarea { height: 80px; }
        button { padding: 6px 20px; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #fafafa; }
        .delete { color: #c00; text-decoration: none;}
        .delete:hover { text-decoration: underline;}
        .msg { color: red; margin-bottom: 12px;}
        .back { margin-top: 16px; display: block;}
    </style>
</head>
<body>
<div class="container">
    <h1>公告管理</h1>
    <?php if (!empty($msg)): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="post">
        <label>公告标题：<input type="text" name="title" required></label>
        <label>公告内容：<textarea name="content" required></textarea></label>
        <button type="submit">发布公告</button>
    </form>
    <h2>最近公告</h2>
    <table>
        <tr><th>标题</th><th>内容</th><th>时间</th><th>操作</th></tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><a class="delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('确定删除？');">删除</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">暂无公告</td></tr>
        <?php endif; ?>
    </table>
    <a class="back" href="../personnel-management/index.php">&lt; 返回首页</a>
</div>
</body>
</html>
<?php $conn->close(); ?>