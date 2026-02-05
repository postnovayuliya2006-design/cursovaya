<?php
session_start();
require '../db.php';
require 'check_admin.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Проверка CSRF
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF ошибка");
    }

    // Получаем данные из формы
    $full_name = trim($_POST['full_name']);
    $position = trim($_POST['position']);
    $expected_salary = $_POST['expected_salary'];
    $photo_url = trim($_POST['photo_url']);
    $user_id = $_SESSION['user_id'];

    // === ЗАГРУЗКА РЕЗЮМЕ ===
    $resumePath = '';
    if (!empty($_FILES['resume_file']['name'])) {
        $allowed = ['pdf','doc','docx','txt'];
        $ext = pathinfo($_FILES['resume_file']['name'], PATHINFO_EXTENSION);

        if (!in_array(strtolower($ext), $allowed)) {
            $message = "<div class='alert alert-danger'>Недопустимый формат файла</div>";
        } else {
            $dir = "../uploads/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $fileName = time().'_'.$user_id.'.'.$ext;
            move_uploaded_file($_FILES['resume_file']['tmp_name'], $dir.$fileName);
            $resumePath = 'uploads/'.$fileName;
        }
    }

    // === Вставка в БД ===
    if (!$message) {
        $sql = "INSERT INTO candidates (full_name, position, resume, expected_salary, photo_url, user_id)
                VALUES (:fn, :pos, :r, :es, :pu, :uid)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fn' => $full_name,
            ':pos' => $position,
            ':r' => $resumePath,
            ':es' => $expected_salary,
            ':pu' => $photo_url,
            ':uid' => $user_id
        ]);

        $message = "<div class='alert alert-success'>Кандидат добавлен</div>";
    }
}

// Генерируем CSRF-токен
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить кандидата</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1>Добавление нового кандидата</h1>
    <a href="index.php" class="btn btn-secondary mb-3">← На главную</a>

    <?= $message ?>

    <form method="POST" class="card p-4 shadow-sm" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="mb-3">
            <label>ФИО кандидата:</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Должность:</label>
            <input type="text" name="position" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Ожидаемая зарплата (руб):</label>
            <input type="number" name="expected_salary" class="form-control" step="0.01" required>
        </div>

        <div class="mb-3">
            <label>Ссылка на фотографию (URL):</label>
            <input type="text" name="photo_url" class="form-control" placeholder="https://...">
            <small class="text-muted">Пока просто вставьте ссылку на фотографию из интернета</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Резюме (PDF / DOC / DOCX):</label>
            <input type="file" name="resume_file" class="form-control" accept=".pdf,.doc,.docx">
            <small class="text-muted">Загрузите файл резюме кандидата</small>
        </div>

        <button type="submit" class="btn btn-success">Сохранить в БД</button>
    </form>

    <!-- Информация о пользователе -->
    <div class="mt-4 card p-3 bg-light">
        <h6>Информация о записи:</h6>
        <p>Кандидат будет сохранен от имени: <strong><?= $_SESSION['user_name'] ?? 'Неизвестный' ?></strong></p>
        <p>User ID в сессии: <code><?= $_SESSION['user_id'] ?? 'нет' ?></code></p>
    </div>
</div>
</body>
</html>
