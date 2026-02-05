<?php
// 1. Начинаем сессию и подключаемся к базе
session_start();
require '../db.php';

// 2. Проверка доступа: Если не вошел — отправляем на вход
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 3. БЕЗОПАСНЫЙ ЗАПРОС (Anti-IDOR)
// Мы выбираем только те заявки, где employer_id совпадает с текущим пользователем.
// Используем JOIN, чтобы получить данные кандидата из таблицы candidates.
$sql = "
    SELECT 
        applications.id as application_id, 
        applications.created_at, 
        applications.status, 
        candidates.full_name, 
        candidates.expected_salary,
        candidates.photo_url,
        candidates.resume
    FROM applications 
    JOIN candidates ON applications.candidate_id = candidates.id 
    WHERE applications.employer_id = ? 
    ORDER BY applications.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$my_applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - Кадровое агентство</title>
    <!-- Подключаем Bootstrap для красоты -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Кадровое Агентство</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Вы вошли как: <b><?= htmlspecialchars($_SESSION['username'] ?? $_SESSION['user_role'] ?? 'Работодатель') ?></b>
                </span>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">На главную</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="mb-0">Мои заявки на кандидатов</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Проверка: Есть ли заявки вообще? -->
                        <?php if (count($my_applications) > 0): ?>
                            
                            <div class="row">
                                <?php foreach ($my_applications as $application): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100">
                                            <!-- Фото кандидата -->
                                            <?php $photo = $application['photo_url'] ?: 'https://via.placeholder.com/300'; ?>
                                            <img src="<?= htmlspecialchars($photo) ?>" class="card-img-top" alt="Фото кандидата" style="height: 200px; object-fit: cover;">
                                            
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($application['full_name']) ?></h5>
                                                <p class="card-text"><?= htmlspecialchars(mb_substr($application['resume'], 0, 150)) ?>...</p>
                                                <p class="card-text fw-bold text-primary"><?= number_format($application['expected_salary'], 0, ',', ' ') ?> ₽</p>
                                                
                                                <!-- Статус с цветным бейджиком -->
                                                <div class="mb-3">
                                                    <?php 
                                                    // Логика цвета для статуса
                                                    $status_color = 'secondary';
                                                    if ($application['status'] == 'new') $status_color = 'primary';
                                                    if ($application['status'] == 'processing') $status_color = 'warning';
                                                    if ($application['status'] == 'done') $status_color = 'success';
                                                    ?>
                                                    <span class="badge bg-<?= $status_color ?> fs-6">
                                                        <?= htmlspecialchars($application['status']) ?>
                                                    </span>
                                                </div>
                                                
                                                <!-- Дата заявки -->
                                                <p class="card-text text-muted small">
                                                    Заявка оставлена: <?= date('d.m.Y H:i', strtotime($application['created_at'])) ?>
                                                </p>
                                            </div>
                                            <div class="card-footer bg-white d-flex gap-2">
    <!-- Кнопка "Подробнее о кандидате" -->
    <a href="#" class="btn btn-outline-primary w-50">Подробнее о кандидате</a>

    <!-- Новая кнопка "Редактировать" -->
<a href="edit_candidate.php?id=<?= $application['candidate_id'] ?>" class="btn btn-warning w-50">
    ✏️ Редактировать
</a>

</div>

                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php else: ?>
                            <!-- Если заявок нет -->
                            <div class="text-center py-5">
                                <h4 class="text-muted">Вы еще не оставляли заявок на кандидатов.</h4>
                                <p class="text-muted">Найдите подходящего специалиста в нашей базе кандидатов.</p>
                                <a href="index.php" class="btn btn-primary mt-3">Перейти к базе кандидатов</a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>