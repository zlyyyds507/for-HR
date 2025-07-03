<?php
$host = 'localhost';
$user = 'gym'; // 默认一般是 root
$pass = '314159'; //
$dbname = '人事管理系统数据库';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('数据库连接失败: ' . $conn->connect_error);
}
?>