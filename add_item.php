<?php 
session_start();
require '../db.php';
require 'check_admin.php';

// 🔥 ВКЛЮЧАЕМ ОШИБКИ (чтобы больше не было белого экрана)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name']);
    $position = trim($_POST['position']);
    $expected_salary = $_POST['expected_salary'];

    $photo_path = null;
    $resume_path = null;

    // ================= PHOTO =================
    if (!empty($_FILES['photo']['name'])) {

        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            $message = "❌ Фото должно быть JPG, PNG или GIF";
        } else {

            $fileName = uniqid('photo_') . '.' . $ext;
            $fullPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $fullPath)) {
                $photo_path = $fullPath;
            } else {
                $message = "❌ Ошибка загрузки фото";
            }
        }
    }

    // ================= RESUME =================
    if (!empty($_FILES['resume']['name'])) {

        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            $message = "❌ Резюме должно быть PDF";
        } else {

            $fileName = uniqid('resume_') . '.pdf';
            $fullPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['resume']['tmp_name'], $fullPath)) {
                $resume_path = $fullPath;
            } else {
                $message = "❌ Ошибка загрузки резюме";
            }
        }
    }

    // ================= DB =================
    if ($photo_path && $resume_path && !$message) {

        $stmt = $pdo->prepare("
            INSERT INTO candidates 
            (full_name, position, expected_salary, photo_url, resume_pdf)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $full_name,
            $position,
            $expected_salary,
            $photo_path,
            $resume_path
        ]);

        $message = "✅ Кандидат успешно добавлен!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Добавить кандидата</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">

    <a href="profile.php" class="btn btn-secondary mb-3">← Назад</a>

    <h2>Добавить кандидата</h2>

    <?php if ($message): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4">

        <div class="mb-3">
            <label>ФИО</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Должность</label>
            <input type="text" name="position" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Ожидаемая зарплата</label>
            <input type="number" name="expected_salary" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Фото</label>
            <input type="file" name="photo" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Резюме (PDF)</label>
            <input type="file" name="resume" class="form-control" required>
        </div>

        <button class="btn btn-success">Добавить</button>

    </form>
</div>

</body>
</html>