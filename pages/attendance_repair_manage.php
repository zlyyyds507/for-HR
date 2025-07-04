<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_role('admin');
require_once __DIR__ . '/../personnel-management/db/connect.php';

// 审核处理逻辑
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['audit_id'])) {
    $audit_id = intval($_POST['audit_id']);
    $status = $_POST['status'] ?? '';
    $reply = trim($_POST['reply'] ?? '');
    $reviewer = $_SESSION['user'] ?? '';
    // 查补卡申请
    $stmt = $conn->prepare("SELECT * FROM attendance_repair WHERE id=?");
    $stmt->bind_param("i", $audit_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        $message = "未找到补卡申请";
    } elseif ($status !== '已通过' && $status !== '已拒绝') {
        $message = "请选择审核结果";
    } else {
        // 审核通过要写入/更新attendance表
        if ($status === '已通过') {
            if ($row['type'] === 'check_in') {
                // 补上班卡
                $stmt = $conn->prepare("INSERT INTO attendance (employee_id, date, check_in, status) VALUES (?, ?, ?, '补卡中') ON DUPLICATE KEY UPDATE check_in=?, status='补卡中'");
                $stmt->bind_param("isss", $row['employee_id'], $row['date'], $row['repair_time'], $row['repair_time']);
            } else {
                // 补下班卡
                $stmt = $conn->prepare("INSERT INTO attendance (employee_id, date, check_out, status) VALUES (?, ?, ?, '补卡中') ON DUPLICATE KEY UPDATE check_out=?, status='补卡中'");
                $stmt->bind_param("isss", $row['employee_id'], $row['date'], $row['repair_time'], $row['repair_time']);
            }
            $stmt->execute();
            $stmt->close();
        }
        $stmt = $conn->prepare("UPDATE attendance_repair SET status=?, reply=?, reviewer=?, reviewed_at=NOW() WHERE id=?");
        $stmt->bind_param("sssi", $status, $reply, $reviewer, $audit_id);
        if ($stmt->execute()) {
            $message = "审核成功";
            // 审核后刷新本页
            header("Location: attendance_repair_manage.php");
            exit;
        } else {
            $message = "审核失败：" . $stmt->error;
        }
        $stmt->close();
    }
}

// 查询补卡申请列表
$sql = "SELECT ar.*, e.name FROM attendance_repair ar JOIN employees e ON ar.employee_id = e.id ORDER BY ar.status='待审核' DESC, ar.created_at DESC";
$res = $conn->query($sql);

// 获取当前要审核的id，如果有（GET参数）
$review_id = isset($_GET['review_id']) ? intval($_GET['review_id']) : 0;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>补卡管理</title>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background:#f8f8f8;}
        .container { width:900px;margin:40px auto;background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 10px #eee;}
        h2 { text-align:center;}
        table { width:100%; border-collapse:collapse; margin-top:16px;}
        th, td { padding:8px 10px; border:1px solid #eee;}
        .status-pending { color: #c90;}
        .status-passed { color: #26a042;}
        .status-reject { color: #c00;}
        .links { margin-top: 22px; text-align: center; }
        .links a { margin: 0 10px; color: #234599; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
        .audit-row { background: #f6faff; }
        .msg { color: #c00; text-align: center; margin-bottom: 10px;}
        .audit-form select, .audit-form textarea { width: 95%; margin-top: 6px;}
        .audit-form button { margin-top: 10px; padding: 6px 18px; }
    </style>
</head>
<body>
<div class="container">
    <h2>补卡管理</h2>
    <?php if ($message): ?><div class="msg"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <table>
        <tr>
            <th>员工</th>
            <th>补卡日期</th>
            <th>类型</th>
            <th>补卡时间</th>
            <th>原因</th>
            <th>状态</th>
            <th>审核回复</th>
            <th>申请时间</th>
            <th>操作</th>
        </tr>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= $row['type']=='check_in'?'上班补卡':'下班补卡' ?></td>
            <td><?= htmlspecialchars($row['repair_time']) ?></td>
            <td><?= htmlspecialchars($row['reason']) ?></td>
            <td class="<?php
                if ($row['status']=='待审核') echo 'status-pending';
                elseif ($row['status']=='已通过') echo 'status-passed';
                else echo 'status-reject';
            ?>"><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['reply']) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td>
                <?php if ($row['status']=='待审核'): ?>
                    <?php if ($review_id == $row['id']): ?>
                        <!-- 审核表单 -->
                        <form method="post" class="audit-form">
                            <input type="hidden" name="audit_id" value="<?= $row['id'] ?>">
                            <select name="status" required>
                                <option value="">请选择</option>
                                <option value="已通过">通过</option>
                                <option value="已拒绝">拒绝</option>
                            </select><br>
                            <textarea name="reply" rows="2" placeholder="审核说明（可选）"></textarea><br>
                            <button type="submit">提交</button>
                            <a href="attendance_repair_manage.php" style="margin-left:12px;">取消</a>
                        </form>
                    <?php else: ?>
                        <a href="attendance_repair_manage.php?review_id=<?= $row['id'] ?>">审核</a>
                    <?php endif; ?>
                <?php else: ?>
                    --
                <?php endif; ?>
            </td>
        </tr>
        <?php if ($review_id == $row['id']): ?>
        <tr class="audit-row"><td colspan="9" style="text-align:center;">正在审核该条申请…</td></tr>
        <?php endif; ?>
        <?php endwhile; ?>
    </table>
    <div class="links">
        <a href="../personnel-management/index.php">返回首页</a>
    </div>
</div>
</body>
</html>