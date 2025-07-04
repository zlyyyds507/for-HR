<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_once __DIR__ . '/../personnel-management/db/connect.php';

$att_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$emp_id = isset($_GET['emp_id']) ? intval($_GET['emp_id']) : 0;

if ($att_id > 0 && $emp_id > 0) {
    // 获取文件名
    $stmt = $conn->prepare("SELECT file_name FROM employee_attachments WHERE id=?");
    $stmt->bind_param("i", $att_id);
    $stmt->execute();
    $stmt->bind_result($file_name);
    $stmt->fetch();
    $stmt->close();

    // 删除数据库记录
    $stmt2 = $conn->prepare("DELETE FROM employee_attachments WHERE id=?");
    $stmt2->bind_param("i", $att_id);
    $stmt2->execute();
    $stmt2->close();

    // 删除文件
    if ($file_name && file_exists(__DIR__ . '/../uploads/' . $file_name)) {
        @unlink(__DIR__ . '/../uploads/' . $file_name);
    }
    header("Location: employee_detail.php?id=$emp_id&msg=" . urlencode("附件删除成功！"));
    exit;
}
header("Location: employee_detail.php?id=$emp_id&msg=" . urlencode("无效操作"));
exit;