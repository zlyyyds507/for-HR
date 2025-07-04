<?php
require_once __DIR__ . '/../personnel-management/auth.php';
require_login();
require_once __DIR__ . '/../personnel-management/db/connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0 && isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename = 'photo_' . $id . '_' . time() . '.' . $ext;
    $target = __DIR__ . '/../uploads/' . $filename;
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
        // 更新员工表
        $stmt = $conn->prepare("UPDATE employees SET photo=? WHERE id=?");
        $stmt->bind_param("si", $filename, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: employee_detail.php?id=$id&msg=" . urlencode("照片上传成功！"));
        exit;
    } else {
        header("Location: employee_detail.php?id=$id&msg=" . urlencode("照片上传失败！"));
        exit;
    }
}
header("Location: employee_detail.php?id=$id&msg=" . urlencode("无效操作"));
exit;