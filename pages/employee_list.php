<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../personnel-management/login.php");
    exit;
}
?>

<?php
require_once __DIR__ . '/../personnel-management/db/connect.php';

// 分页配置
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = 5;
$offset = ($page - 1) * $pageSize;

// 搜索配置
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$where = '';
$params = [];
$types = '';
$sqlCount = "SELECT COUNT(*) FROM employees";
$sql = "SELECT * FROM employees";

// 拼接搜索条件
if ($keyword !== '') {
    $where = " WHERE name LIKE ? OR department LIKE ? ";
    $params[] = '%' . $keyword . '%';
    $params[] = '%' . $keyword . '%';
    $types = 'ss';
    $sqlCount .= $where;
    $sql .= $where;
}

// 获取总记录数
$total = 0;
if ($types) {
    $stmt = $conn->prepare($sqlCount);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
} else {
    $res = $conn->query($sqlCount);
    $total = $res->fetch_row()[0];
}

// 查询当前页数据
$sql .= " ORDER BY id DESC LIMIT ?, ?";
$params2 = $params;
$params2[] = $offset;
$params2[] = $pageSize;
$types2 = $types . "ii";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types2, ...$params2);
} else {
    $stmt->bind_param("ii", $offset, $pageSize);
}
$stmt->execute();
$result = $stmt->get_result();

$totalPages = max(1, ceil($total / $pageSize));
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>员工列表</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body { font-family: Arial, "微软雅黑", sans-serif; }
        table { border-collapse: collapse; width: 90%; margin: 20px auto; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: center; }
        th { background: #f7f7f7; }
        h1 { text-align: center; }
        .add-btn { margin-left: 5%; display: inline-block; margin-bottom: 10px; }
        .search-form { text-align: center; margin-bottom: 20px; }
        .pagination { text-align: center; margin: 20px; }
        .pagination a, .pagination strong { margin: 0 4px; text-decoration: none; }
    </style>
</head>
<body>
    <h1>员工列表</h1>
    <div class="add-btn">
        <a href="add_employee.php">➕ 添加新员工</a>
    </div>
    <form class="search-form" method="get">
        关键词：
        <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
        <button type="submit">搜索</button>
    </form>
    <table>
        <tr>
            <th>ID</th>
            <th>姓名</th>
            <th>职位</th>
            <th>部门</th>
            <th>入职时间</th>
            <th>薪资</th>
            <th>操作</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                    <td><?php echo $row['hire_date']; ?></td>
                    <td><?php echo $row['salary']; ?></td>
                    <td>
                        <a href="edit_employee.php?id=<?php echo $row['id']; ?>">编辑</a>
                        <!-- <a href="delete_employee.php?id=<?php echo $row['id']; ?>" onclick="return confirm('确认要删除这位员工吗？');">删除</a> -->
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">暂无员工信息</td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- 分页导航 -->
    <div class="pagination">
        <?php
        $baseUrl = "?";
        if ($keyword !== '') {
            $baseUrl .= "keyword=" . urlencode($keyword) . "&";
        }
        if ($page > 1) {
            echo '<a href="' . $baseUrl . 'page=1">首页</a>';
            echo '<a href="' . $baseUrl . 'page=' . ($page - 1) . '">上一页</a>';
        }
        // 显示页码链接（前后±2页）
        for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++) {
            if ($i == $page) {
                echo '<strong>' . $i . '</strong>';
            } else {
                echo '<a href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a>';
            }
        }
        if ($page < $totalPages) {
            echo '<a href="' . $baseUrl . 'page=' . ($page + 1) . '">下一页</a>';
            echo '<a href="' . $baseUrl . 'page=' . $totalPages . '">末页</a>';
        }
        ?>
    </div>
</body>
</html>