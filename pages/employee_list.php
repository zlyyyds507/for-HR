<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();

// 数据库连接
$servername = "localhost";
$username = "gym";
$password = "314159";
$dbname = "人事管理系统数据库";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 处理搜索和状态筛选参数
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$search_sql = '';
$params = [];
$param_types = '';

if ($search !== '') {
    $search_sql .= ($search_sql ? ' AND ' : ' WHERE ') . "(name LIKE ? OR position LIKE ? OR department LIKE ?)";
    $search_value = "%{$search}%";
    $params = [$search_value, $search_value, $search_value];
    $param_types = 'sss';
}
if ($status_filter === '在职' || $status_filter === '离职') {
    $search_sql .= ($search_sql ? ' AND ' : ' WHERE ') . "status=?";
    $params[] = $status_filter;
    $param_types .= 's';
}

// 分页参数
$per_page = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// 查询总数
$count_sql = "SELECT COUNT(*) FROM employees" . ($search_sql ? $search_sql : "");
$count_stmt = $conn->prepare($count_sql);
if ($param_types) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$count_stmt->bind_result($total_count);
$count_stmt->fetch();
$count_stmt->close();

$total_pages = ceil($total_count / $per_page);

// 查询员工数据
$sql = "SELECT id, name, position, department, status FROM employees" . ($search_sql ? $search_sql : "") . " ORDER BY id ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($param_types) {
    $bind_values = array_merge($params, [$per_page, $offset]);
    $stmt->bind_param($param_types . "ii", ...$bind_values);
} else {
    $stmt->bind_param("ii", $per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

$role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : 'user';

// 部门分布统计
$dept_result = $conn->query("SELECT department, COUNT(*) AS num FROM employees WHERE status='在职' GROUP BY department");
$dept_data = [];
while($row = $dept_result->fetch_assoc()) $dept_data[] = $row;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>员工列表</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; text-align: center; margin-top: 50px; }
        table { margin: 0 auto; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px 16px; }
        th { background: #f2f2f2; }
        a { color: #3366cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .search-box { margin-bottom: 24px; }
        .pagination { margin-top: 24px; }
        .pagination a, .pagination span { margin: 0 4px; padding: 4px 8px; border: 1px solid #ccc; text-decoration: none; color: #333; }
        .pagination .current { background: #3366cc; color: #fff; border-color: #3366cc; }
        .chart-wrap { width: 400px; margin: 0 auto 30px auto; background:#fafbff; padding:16px 10px 8px 10px; border-radius:8px;}
        .status-on { color: #26a042; }
        .status-off { color: #c00; }
    </style>
</head>
<body>
    <h1>员工列表</h1>
    <!-- 部门分布可视化统计 -->
    <div class="chart-wrap">
        <div style="text-align:center; font-size:15px; color:#234599;">在职员工部门分布统计</div>
        <canvas id="deptChart" width="400" height="180"></canvas>
    </div>
    <div class="search-box">
        <form method="get" style="display:inline-block;">
            <input type="text" name="search" placeholder="搜索姓名、职位、部门" value="<?= htmlspecialchars($search) ?>" />
            <select name="status">
                <option value="">全部状态</option>
                <option value="在职" <?= $status_filter=='在职'?'selected':''; ?>>在职</option>
                <option value="离职" <?= $status_filter=='离职'?'selected':''; ?>>离职</option>
            </select>
            <button type="submit">搜索/筛选</button>
        </form>
    </div>
    <?php if ($role === 'admin'): ?>
        <a href="add_employee.php">添加新员工</a>
    <?php endif; ?>
    <table>
        <tr>
            <th>ID</th>
            <th>姓名</th>
            <th>职位</th>
            <th>部门</th>
            <th>状态</th>
            <th>操作</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['position']) ?></td>
                <td><?= htmlspecialchars($row['department']) ?></td>
                <td class="<?= $row['status']=='在职'?'status-on':'status-off' ?>">
                    <?= htmlspecialchars($row['status']) ?>
                </td>
                <td>
                    <a href="employee_detail.php?id=<?= $row['id'] ?>">详情</a>
                    <?php if ($role === 'admin' && $row['status'] === '在职'): ?>
                        | <a href="edit_employee.php?id=<?= $row['id'] ?>">编辑</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">暂无员工信息</td></tr>
        <?php endif; ?>
    </table>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&page=1">首页</a>
            <a href="?search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&page=<?= $page-1 ?>">上一页</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="?search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&page=<?= $page+1 ?>">下一页</a>
            <a href="?search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&page=<?= $total_pages ?>">末页</a>
        <?php endif; ?>
    </div>
    <br>
    <a href="../personnel-management/index.php">返回首页</a>
    <script>
    const deptLabels = <?= json_encode(array_column($dept_data, 'department')) ?>;
    const deptNums = <?= json_encode(array_map('intval', array_column($dept_data, 'num'))) ?>;
    const ctx = document.getElementById('deptChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: deptLabels,
            datasets: [{
                data: deptNums,
                backgroundColor: [
                    '#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f','#edc949','#af7aa1','#ff9da7'
                ]
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>