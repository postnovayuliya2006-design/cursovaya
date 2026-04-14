<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
        $errorMsg = "Заполните все поля!";
    }
    elseif ($newPass !== $confirmPass) {
        $errorMsg = "Новый пароль и подтверждение не совпадают!";
    } else {

        // получаем текущий пароль
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($currentPass, $user['password_hash'])) {

            $newHash = password_hash($newPass, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$newHash, $_SESSION['user_id']]);

            $successMsg = "Пароль успешно изменён!";

        } else {
            $errorMsg = "Текущий пароль введён неверно!";
        }
    }
}
?>