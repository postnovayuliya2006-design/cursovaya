<?php
session_start();
require '../db.php';

// 1. Проверка: Вошел ли пользователь?
if (!isset($_SESSION['user_id'])) {
    die("Сначала войдите в систему! <a href='login.php'>Вход</a>");
}

// 2. Получаем ID кандидата из ссылки (например, make_application.php?id=5)
// (int) — это защита от хакеров, превращаем ВСЁ в число. "text" станет 0.
$candidate_id = (int)$_GET['id'];
$employer_id = $_SESSION['user_id'];

if ($candidate_id > 0) {
    // ПРОВЕРКА БЕЗОПАСНОСТИ №2: А существует ли такой кандидат?
    $check = $pdo->prepare("SELECT id FROM candidates WHERE id = ?");
    $check->execute([$candidate_id]);
    $exists = $check->fetch();

    if (!$exists) {
        die("Ошибка: Попытка оставить заявку на несуществующего кандидата! Ваш IP записан.");
    }

    // 3. Создаем заявку (только после проверки)
    $stmt = $pdo->prepare("INSERT INTO applications (employer_id, candidate_id) VALUES (?, ?)");
    try {
        $stmt->execute([$employer_id, $candidate_id]);
        echo "Заявка успешно оформлена! Менеджер свяжется с вами. <a href='index.php'>Вернуться</a>";
    } catch (PDOException $e) {
        echo "Ошибка: " . $e->getMessage();
    }
} else {
    echo "Неверный кандидат.";
}
?>