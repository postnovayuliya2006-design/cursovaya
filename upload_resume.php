<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    die('Доступ запрещен');
}

$uploadDir = 'uploads/resumes/';
$allowedType = 'application/pdf';

if (isset($_FILES['resume'])) {
    $file = $_FILES['resume'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('Ошибка загрузки');
    }

    if ($file['type'] !== $allowedType) {
        die('Разрешены только PDF-файлы');
    }

    $newName = uniqid('cv_') . '.pdf';
    $destination = $uploadDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $stmt = $pdo->prepare(
            "UPDATE users SET resume_file = ? WHERE id = ?"
        );
        $stmt->execute([$destination, $_SESSION['user_id']]);

        echo "Резюме успешно загружено! <a href='profile.php'>Назад</a>";
    }
}
