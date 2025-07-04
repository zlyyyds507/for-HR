<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_once __DIR__ . '/../personnel-management/db/connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0 && isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
    $orig_name = $_FILES['attachment']['name'];
    $ext = pathinfo($orig_name, PATHINFO_EXTENSION);
    $filename = 'att_' . $id . '_' . time() . '.' . $ext;
    $target = __DIR__ . '/../uploads/' . $filename;
    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
        // 插入附件表
        $stmt = $conn->prepare("INSERT INTO employee_attachments (employee_id, file_name, original_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id, $filename, $orig_name);
        $stmt->execute();
        $stmt->close();
        header("Location: employee_detail.php?id=$id&msg=" . urlencode("附件上传成功！"));
        exit;
    } else {
        header("Location: employee_detail.php?id=$id&msg=" . urlencode("附件上传失败！"));
        exit;
    }
}
header("Location: employee_detail.php?id=$id&msg=" . urlencode("无效操作"));
exit;