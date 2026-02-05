<?php
session_start();
require '../db.php';
require 'check_admin.php'; // Только для админа

// Получаем ID кандидата из GET
$id = (int)($_GET['id'] ?? 0);
if ($id === 0) die("Неверный ID");

// Достаём данные кандидата
$stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$id]);
$candidate = $stmt->fetch();

if (!$candidate) die("Кандидат не найден");

// Сообщение об успешном обновлении
$message = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF можно добавить, если нужно
    $full_name = trim($_POST['full_name']);
    $position = trim($_POST['position']);
    $expected_salary = $_POST['expected_salary'];
    $photo_url = trim($_POST['photo_url']);

    $update = $pdo->prepare("
        UPDATE candidates SET
        full_name = :fn,
        position = :pos,
        expected_salary = :es,
        photo_url = :pu
        WHERE id = :id
    ");

    $update->execute([
        ':fn' => $full_name,
        ':pos' => $position,
        ':es' => $expected_salary,
        ':pu' => $photo_url,
        ':id' => $id
    ]);

    $message = "<div class='alert alert-success'>Данные кандидата обновлены</div>";

    // Обновляем переменную, чтобы форма показала свежие данные
    $candidate['full_name'] = $full_name;
    $candidate['position'] = $position;
    $candidate['expected_salary'] = $expected_salary;
    $candidate['photo_url'] = $photo_url;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование кандидата</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1>Редактирование кандидата</h1>
    <a href="index.php" class="btn btn-secondary mb-3">← На главную</a>

    <?= $message ?>

    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label>ФИО кандидата:</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($candidate['full_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Должность:</label>
            <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($candidate['position']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Ожидаемая зарплата (руб):</label>
            <input type="number" name="expected_salary" class="form-control" step="0.01" value="<?= htmlspecialchars($candidate['expected_salary']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Ссылка на фотографию (URL):</label>
            <input type="text" name="photo_url" class="form-control" value="<?= htmlspecialchars($candidate['photo_url']) ?>">
        </div>

        <button type="submit" class="btn btn-success">Обновить кандидата</button>
    </form>
</div>
</body>
</html>
