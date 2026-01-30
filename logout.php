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
                    <?php $img = $candidate['photo_url'] ?: 'https://via.placeholder.com/300'; ?>
                    <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="Фото" style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($candidate['full_name']) ?></h5>
                        <p class="card-text text-truncate"><?= htmlspecialchars($candidate['resume']) ?></p>
                        <p class="card-text fw-bold text-primary"><?= $candidate['expected_salary'] ?> ₽</p>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <a href="#" class="btn btn-primary w-100">Подробнее</a>
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