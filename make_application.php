<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Нет доступа");
}

$user_id = $_SESSION['user_id'];
$candidate_id = (int)($_POST['candidate_id'] ?? 0);

// Проверка на дубликат
$stmt = $pdo->prepare("SELECT id FROM applications WHERE user_id = ? AND candidate_id = ?");
$stmt->execute([$user_id, $candidate_id]);

if ($stmt->rowCount()) {
    header("Location: index.php");
    exit;
}

// Добавление
$stmt = $pdo->prepare("INSERT INTO applications (user_id, candidate_id) VALUES (?, ?)");
$stmt->execute([$user_id, $candidate_id]);

header("Location: index.php");