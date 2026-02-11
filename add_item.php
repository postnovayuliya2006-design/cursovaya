<?php
session_start();
require '../db.php';
require 'check_admin.php'; // только админ может добавлять кандидатов

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name']);
    $position = trim($_POST['position']);
    $expected_salary = trim($_POST['expected_salary']);
    $photo_url = trim($_POST['photo_url']);

    if (empty($full_name) || empty($position) || empty($expected_salary)) {
        $error = "Заполните все обязательные поля.";
    } else {

        // ===== ОБРАБОТКА PDF =====
        if (!isset($_FILES['resume_pdf']) || $_FILES['resume_pdf']['error'] !== 0) {
            $error = "Загрузите PDF-файл.";
        } else {

            $tmpName = $_FILES['resume_pdf']['tmp_name'];
            $fileName = $_FILES['resume_pdf']['name'];
            $fileSize = $_FILES['resume_pdf']['size'];

            // Проверка MIME-типа
            $fileType = mime_content_type($tmpName);
            if ($fileType !== 'application/pdf') {
                $error = "Разрешены только PDF-файлы.";
            }

            // Проверка размера (макс 5MB)
            if ($fileSize > 5 * 1024 * 1024) {
                $error = "Файл слишком большой (макс 5MB).";
            }

            if (empty($error)) {

                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9\.]/", "_", $fileName);
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpName, $destination)) {

                    $dbPath = 'uploads/' . $newFileName;

                    $stmt = $pdo->prepare("
                        INSERT INTO candidates
                        (full_name, position, expected_salary, photo_url, resume_pdf)
                        VALUES (?, ?, ?, ?, ?)
                    ");

                    $stmt->execute([
                        $full_name,
                        $position,
                        $expected_salary,
                        $photo_url,
                        $dbPath
                    ]);

                    $message = "Кандидат успешно добавлен!";
                } else {
                    $error = "Ошибка сохранения файла.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить кандидата</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow p-4">
        <h2 class="mb-4">Добавить кандидата</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label">ФИО кандидата *</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Должность *</label>
                <input type="text" name="position" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Ожидаемая зарплата *</label>
                <input type="number" name="expected_salary" step="0.01" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Ссылка на фото (URL)</label>
                <input type="text" name="photo_url" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">PDF резюме *</label>
                <input type="file" name="resume_pdf" accept="application/pdf" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Добавить</button>
            <a href="index.php" class="btn btn-secondary">Назад</a>

        </form>
    </div>
</div>

</body>
</html>
