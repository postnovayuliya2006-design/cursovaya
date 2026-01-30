<?php
session_start();
require '../db.php';
require 'check_admin.php'; // Эту страницу видит только админ!

if (!function_exists('h')) {
    function h($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

$message = '';

// 2. Если нажата кнопка "Сохранить"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $expected_salary = $_POST['expected_salary'];
    $resume  = trim($_POST['resume']);
    $photo_url   = trim($_POST['photo_url']);
    
    // Получаем ID текущего пользователя из сессии
    $user_id = $_SESSION['user_id'] ?? null;

    if (empty($full_name) || empty($expected_salary)) {
        $message = '<div class="alert alert-danger">Заполните ФИО и ожидаемую зарплату!</div>';
    } elseif (!$user_id) {
        $message = '<div class="alert alert-danger">Ошибка: пользователь не авторизован!</div>';
    } else {
        // 3. Сохраняем в Базу Данных С user_id
        $sql = "INSERT INTO candidates (full_name, resume, expected_salary, photo_url, user_id) 
                VALUES (:fn, :r, :es, :pu, :uid)";
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([
                ':fn' => $full_name,
                ':r' => $resume,
                ':es' => $expected_salary,
                ':pu' => $photo_url,
                ':uid' => $user_id
            ]);
            $message = '<div class="alert alert-success">Кандидат успешно добавлен!</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Ошибка БД: ' . $e->getMessage() . '</div>';
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
<body class="p-4">
    <div class="container">
        <h1>Добавление нового кандидата</h1>
        <a href="index.php" class="btn btn-secondary mb-3">← На главную</a>
        
        <?= $message ?>

        <form method="POST" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label>ФИО кандидата:</label>
                <input type="text" name="full_name" class="form-control" required>
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
                <label>Резюме/описание:</label>
                <textarea name="resume" class="form-control" rows="3"></textarea>
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