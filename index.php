<?php
session_start();
require '../db.php';

$user_role = $_SESSION['user_role'] ?? 'guest';
$q = $_GET['q'] ?? '';

// --- ПАГИНАЦИЯ ---
// 1. Номер страницы
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 10; // записей на страницу
$offset = ($page - 1) * $limit;

// 2. Получаем общее количество кандидатов (для навигации)
$total_stmt = $pdo->query("SELECT COUNT(*) FROM candidates");
$total_rows = $total_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// 3. Получаем кандидатов с учетом поиска и лимита
if ($q) {
    $stmt = $pdo->prepare("SELECT * FROM candidates WHERE position LIKE ? OR full_name LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");
    $search = "%$q%";
    $stmt->bindValue(1, $search, PDO::PARAM_STR);
    $stmt->bindValue(2, $search, PDO::PARAM_STR);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->bindValue(4, $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
}

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
            <span class="me-3">Привет!</span>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="admin_panel.php" class="btn btn-outline-danger btn-sm">Админка</a>
                <a href="add_item.php" class="btn btn-success btn-sm">+ Добавить кандидата</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-dark btn-sm">Выйти</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary btn-sm">Войти</a>
            <a href="register.php" class="btn btn-outline-primary btn-sm">Регистрация</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">База кандидатов</h2>

    <!-- Поиск -->
    <div class="card mb-4 p-3 bg-light">
        <form method="GET" action="index.php" class="row g-3">
            <div class="col-md-8">
                <input type="text" name="q" class="form-control" placeholder="Поиск по должности..." value="<?= htmlspecialchars($q) ?>">
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100">Найти</button>
            </div>
        </form>
    </div>

    <div class="row">
        <?php foreach ($candidates as $candidate): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php $photo = $candidate['photo_url'] ?: 'https://via.placeholder.com/300'; ?>
                    <img src="<?= htmlspecialchars($photo) ?>" class="card-img-top" alt="Фото кандидата" style="height: 200px; object-fit: cover;">

                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($candidate['full_name']) ?></h5>
                        <p class="card-text text-muted fw-semibold"><?= htmlspecialchars($candidate['position']) ?></p>

                        <!-- Индикатор резюме -->
                        <?php if (in_array($user_role, ['client', 'admin'])): ?>
                            <?php if (!empty($candidate['resume_pdf'] ?? '')): ?>
                                <span class="badge bg-success mb-2">📄 Резюме доступно</span>
                            <?php else: ?>
                                <span class="badge bg-secondary mb-2">Резюме отсутствует</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <p class="card-text fw-bold text-primary"><?= number_format($candidate['expected_salary'], 2) ?> ₽</p>
                        <p class="card-text text-muted small">Добавлен: <?= date('d.m.Y', strtotime($candidate['created_at'])) ?></p>
                    </div>

                    <div class="card-footer bg-white border-top-0">
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'client'): ?>
                            <a href="make_application.php?id=<?= $candidate['id'] ?>" class="btn btn-success w-100">Оставить заявку</a>
                        <?php else: ?>
                            <a href="make_application.php?id=<?= $candidate['id'] ?>" class="btn btn-primary w-100">Подробнее</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (count($candidates) === 0): ?>
            <p class="text-muted">Кандидатов пока нет. Зайдите под админом и добавьте их.</p>
        <?php endif; ?>
    </div>

    <!-- Кнопки пагинации -->
    <nav>
        <ul class="pagination justify-content-center mt-4">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= $q ? '&q='.urlencode($q) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>

</div>
</body>
</html>
