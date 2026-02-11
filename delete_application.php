<?php
session_start();
require '../db.php';
require 'check_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF Attack blocked");
    }

    $id = (int)$_POST['id'];

    // Soft delete: ставим is_deleted = 1
    $stmt = $pdo->prepare("UPDATE applications SET is_deleted = 1 WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: admin_applications.php");
    exit;
}
?>
