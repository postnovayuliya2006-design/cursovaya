<?php
session_start();
require '../db.php';
require 'check_admin.php'; // или проверка работодателя

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = (int)$_POST['application_id'];
    $new_status = $_POST['status'] ?? '';

    $valid_statuses = ['new', 'processing', 'done'];
    if (!in_array($new_status, $valid_statuses)) {
        die("Неверный статус");
    }

    // Проверка CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF атака заблокирована");
    }

    // Обновление статуса
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $application_id]);

    // Заглушка уведомления
    // В реальном проекте можно отправить email, SMS или пуш
    $_SESSION['flash_message'] = "Статус заявки обновлен на '{$new_status}'";

    header("Location: profile.php");
    exit;
}
?>
