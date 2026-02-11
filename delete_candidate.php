<?php
session_start();
require '../db.php';
require 'check_admin.php'; // проверка, что вошёл админ и генерация CSRF токена

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Проверка CSRF токена
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF атака заблокирована!");
    }

    // 2. Получаем ID кандидата
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        // 3. Удаляем все заявки кандидата
        $stmt = $pdo->prepare("DELETE FROM applications WHERE candidate_id = ?");
        $stmt->execute([$id]);

        // 4. Удаляем самого кандидата
        $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
        $stmt->execute([$id]);
    }

    // 5. Перенаправляем обратно на админку
    header("Location: profile.php");
    exit;
} else {
    die("Удаление возможно только методом POST");
}
?>
