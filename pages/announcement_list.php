<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_once __DIR__ . '/../personnel-management/db/connect.php';

// 查询所有公告
$result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>全部公告</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background: #f8f8f8; }
        .container { width: 700px; margin: 40px auto; background: #fff; border-radius: 6px; box-shadow: 0 2px 10px #eee; padding: 30px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #fafafa; }
        .back { margin-top: 16px; display: block; }
        .ann-title { font-weight: bold; color: #234599; font-size: 16px;}
        .ann-content { margin: 4px 0; }
        .ann-time { color: #888; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">
    <h1>全部公告</h1>
    <table>
        <tr><th style="width:30%;">标题</th><th>内容</th><th style="width:18%;">时间</th></tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="ann-title"><?= htmlspecialchars($row['title']) ?></td>
                    <td class="ann-content"><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                    <td class="ann-time"><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3" style="text-align:center;">暂无公告</td></tr>
        <?php endif; ?>
    </table>
    <a class="back" href="../personnel-management/index.php">&lt; 返回首页</a>
</div>
</body>
</html>
<?php $conn->close(); ?>