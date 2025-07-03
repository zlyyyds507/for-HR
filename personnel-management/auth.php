<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user'])) {
        header("Location: ../personnel-management/login.php");
        exit;
    }
}

function require_role($role) {
    if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== strtolower(trim($role))) {
        echo "无权限操作";
        exit;
    }
}
?>