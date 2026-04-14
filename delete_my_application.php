<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Нет доступа");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_SESSION['user_id'];
    $candidate_id = (int)($_POST['candidate_id'] ?? 0);

    // Удаляем только свою заявку (Anti-IDOR)
    $stmt = $pdo->prepare("
        DELETE FROM applications 
        WHERE user_id = ? AND candidate_id = ?
    ");
    $stmt->execute([$user_id, $candidate_id]);

    header("Location: index.php");
    exit;
}