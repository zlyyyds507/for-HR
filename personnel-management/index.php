<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/db/connect.php';

// 获取当前登录用户名和角色
$username = $_SESSION['user'] ?? '';
$role = $_SESSION['role'] ?? 'user';

// 中文角色显示
$role_text = ($role === 'admin') ? '管理员' : '普通员工';

// 查询个人信息（假设username和员工表username字段绑定）
$emp_info = null;
if ($username) {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $emp_info = $res->fetch_assoc();
    $stmt->close();
}

// 查询公告，只显示3条
$announcements = [];
$result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
while($row = $result->fetch_assoc()) $announcements[] = $row;

// 查询公告总数（用于判断是否需要显示“更多”按钮）
$count_result = $conn->query("SELECT COUNT(*) AS total FROM announcements");
$count_row = $count_result->fetch_assoc();
$announcement_total = $count_row['total'];

// 管理员考勤异常提醒（近7天）
$attendance_abnormal = [];
if ($role === 'admin') {
    $sql = "SELECT e.name, a.date, a.status 
            FROM attendance a 
            JOIN employees e ON a.employee_id = e.id 
            WHERE a.status <> '正常' AND a.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ORDER BY a.date DESC LIMIT 10";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) $attendance_abnormal[] = $row;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>人事管理系统首页</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; background: #f8f8f8; }
        .container { width: 700px; margin: 40px auto; background: #fff; border-radius: 6px; box-shadow: 0 2px 10px #eee; padding: 30px; }
        h1 { text-align: center; }
        .userinfo { margin-bottom: 30px; padding: 16px; background: #fafafa; border-radius: 6px; }
        .actions a { margin-right: 24px; }
        .actions { margin: 24px 0; }
        table { border-collapse: collapse; width: 100%; margin-top: 26px;}
        th, td { border: 1px solid #ccc; padding: 8px 12px; }
        th { background: #f2f2f2; }
        .logout { float: right; font-size: 15px; }
        .welcome { float: left; font-size: 15px; }
        .clearfix::after { content: ""; display: table; clear: both; }
        .ann { background: #f6f9ff; border: 1px solid #e4eaf3; border-radius: 6px; padding: 14px 18px; margin-bottom: 24px; }
        .ann-title { font-weight: bold; color: #234599; }
        .ann-content { margin: 6px 0 12px 0; }
        .ann-time { color: #888; font-size: 12px; }
        .more-ann { text-align: right; }
        .more-ann a { font-size: 14px; color: #234599; text-decoration: none;}
        .more-ann a:hover { text-decoration: underline;}
        .att-abn { background:#fffbe8;padding:14px 18px;margin-bottom:18px;border:1px solid #ffe58f; border-radius: 6px;}
        .att-abn-title { font-weight:bold; color:#bfa100;}
        .att-abn-list { margin: 7px 0 0 0; padding-left: 18px; }
    </style>
</head>
<body>
<div class="container">
    <div class="clearfix">
        <span class="welcome">欢迎，<?= htmlspecialchars($username) ?>（<?= $role_text ?>）</span>
        <a class="logout" href="logout.php">退出登录</a>
    </div>
    <h1>人事管理系统首页</h1>

    <?php if ($role === 'admin' && count($attendance_abnormal) > 0): ?>
        <div class="att-abn">
            <div class="att-abn-title">考勤异常提醒（近7天）</div>
            <ul class="att-abn-list">
                <?php foreach($attendance_abnormal as $abn): ?>
                    <li><?= htmlspecialchars($abn['name']) ?> <?= htmlspecialchars($abn['date']) ?> (<?= htmlspecialchars($abn['status']) ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="actions">
        <?php if ($role === 'admin'): ?>
            <a href="../pages/employee_list.php">员工列表管理</a>
            <a href="../pages/announcement_manage.php">公告管理</a>
            <a href="reset_user_password.php">重置用户密码</a>
            <!-- 请假管理入口（管理员） -->
            <a href="../pages/leave_list.php">请假管理/审核</a>
            <a href="../pages/leave_stats.php">请假统计</a>
            <!-- 考勤管理入口（管理员） -->
            <a href="../pages/attendance_list.php">考勤记录</a>
            <a href="../pages/attendance_stats.php">考勤统计</a>
            <a href="../pages/attendance_repair_manage.php">补卡管理</a>
        <?php else: ?>
            <a href="../pages/employee_list.php">查看员工列表</a>
            <!-- 请假入口（普通员工） -->
            <a href="../pages/leave_apply.php">申请请假</a>
            <a href="../pages/leave_list.php">我的请假记录</a>
            <!-- 考勤入口（普通员工） -->
            <a href="../pages/attendance_checkin.php">考勤打卡</a>
            <a href="../pages/attendance_repair.php">补卡申请</a>
            <a href="../pages/attendance_list.php">我的考勤</a>
        <?php endif; ?>
        <a href="change_password.php">修改密码</a>
    </div>

    <!-- 公告栏 -->
    <?php if (count($announcements) > 0): ?>
        <div class="ann">
            <div style="font-size:17px;color:#234599;margin-bottom:4px;">最新公告</div>
            <?php foreach($announcements as $a): ?>
                <div class="ann-title"><?= htmlspecialchars($a['title']) ?></div>
                <div class="ann-content"><?= nl2br(htmlspecialchars($a['content'])) ?></div>
                <div class="ann-time"><?= htmlspecialchars($a['created_at']) ?></div>
                <hr style="border:none;border-top:1px dashed #ccc;">
            <?php endforeach; ?>
            <?php if ($announcement_total > 3): ?>
                <div class="more-ann">
                    <a href="../pages/announcement_list.php">更多公告 &gt;&gt;</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($emp_info): ?>
        <div class="userinfo">
            <h3>我的个人信息</h3>
            <table>
                <tr><th>姓名</th><td><?= htmlspecialchars($emp_info['name']) ?></td></tr>
                <tr><th>部门</th><td><?= htmlspecialchars($emp_info['department']) ?></td></tr>
                <tr><th>职位</th><td><?= htmlspecialchars($emp_info['position']) ?></td></tr>
                <tr><th>联系方式</th><td><?= htmlspecialchars($emp_info['contact']) ?></td></tr>
                <tr><th>入职状态</th><td><?= htmlspecialchars($emp_info['status']) ?></td></tr>
                <tr>
                    <th>操作</th>
                    <td>
                        <a href="../pages/employee_detail.php?id=<?= $emp_info['id'] ?>">查看/编辑详细信息</a>
                    </td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <div style="margin-top: 36px; text-align: center; color: #aaa;">
        &copy; <?= date('Y') ?> 人事管理系统
    </div>
</div>
</body>
</html>