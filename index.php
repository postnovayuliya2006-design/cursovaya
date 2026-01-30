<?php
session_start();
require '../db.php';

// 1. Получаем всех кандидатов из базы
// ORDER BY id DESC означает "сначала новые"
$stmt = $pdo->query("SELECT * FROM candidates ORDER BY id DESC");
$candidates = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная страница</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Навигация -->
<nav class="navbar navbar-light bg-light px-4 mb-4 shadow-sm">
    <span class="navbar-brand mb-0 h1">Кадровое Агентство</span>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Если вошел -->
            <span class="me-3">Привет!</span>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="admin_panel.php" class="btn btn-outline-danger btn-sm">Админка</a>
                <a href="add_item.php" class="btn btn-success btn-sm">+ Добавить кандидата</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-dark btn-sm">Выйти</a>
        <?php else: ?>
            <!-- Если гость -->
            <a href="login.php" class="btn btn-primary btn-sm">Войти</a>
            <a href="register.php" class="btn btn-outline-primary btn-sm">Регистрация</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">База кандидатов</h2>
    
    <div class="row">
        <?php foreach ($candidates as $candidate): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <!-- Если фотографии нет, ставим заглушку -->
                    <?php $photo = $candidate['photo_url'] ?: 'https://via.placeholder.com/300'; ?>
                    <img src="<?= htmlspecialchars($photo) ?>" class="card-img-top" alt="Фото кандидата" style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body">
                        <h5 class="card-title"><?= h($candidate['full_name']) ?></h5>
                        <p class="card-text"><?= h($candidate['resume']) ?></p>
                        <p class="card-text fw-bold text-primary"><?= $candidate['expected_salary'] ?> ₽</p>
                        <p class="card-text text-muted small">Добавлен: <?= date('d.m.Y', strtotime($candidate['created_at'])) ?></p>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <div class="card-footer bg-white border-top-0">
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'client'): ?>
        <a href="make_application.php?id=<?= $candidate['id'] ?>" class="btn btn-success w-100">Оставить заявку</a>
    <?php else: ?>
        <a href="make_application.php?id=<?= $candidate['id'] ?>" class="btn btn-primary w-100">Подробнее</a>
    <?php endif; ?>
</div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (count($candidates) === 0): ?>
            <p class="text-muted">Кандидатов пока нет. Зайдите под админом и добавьте их.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>