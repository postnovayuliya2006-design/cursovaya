<?php
session_start();
require '../db.php';

$user_role = $_SESSION['user_role'] ?? 'guest';
$user_id = $_SESSION['user_id'] ?? 0;

$q = $_GET['q'] ?? '';

// --- ПАГИНАЦИЯ ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 5;
$offset = ($page - 1) * $limit;

// --- ОБЩЕЕ КОЛ-ВО ---
$total_stmt = $pdo->query("SELECT COUNT(*) FROM candidates");
$total_rows = $total_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// --- ПОЛУЧЕНИЕ КАНДИДАТОВ ---
if ($q) {
    $stmt = $pdo->prepare("
        SELECT * FROM candidates 
        WHERE position LIKE ? OR full_name LIKE ? 
        ORDER BY id DESC 
        LIMIT ? OFFSET ?
    ");
    $search = "%$q%";
    $stmt->bindValue(1, $search, PDO::PARAM_STR);
    $stmt->bindValue(2, $search, PDO::PARAM_STR);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->bindValue(4, $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("
        SELECT * FROM candidates 
        ORDER BY id DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
}

$candidates = $stmt->fetchAll();

// --- ЗАЯВКИ ПОЛЬЗОВАТЕЛЯ (ВАЖНО!) ---
$user_applications = [];

if ($user_id && $user_role === 'client') {
    $stmt = $pdo->prepare("SELECT candidate_id FROM applications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_applications = array_column($stmt->fetchAll(), 'candidate_id');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная страница</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- НАВИГАЦИЯ -->
<nav class="navbar navbar-light bg-light px-4 mb-4 shadow-sm">
    <span class="navbar-brand mb-0 h1">Кадровое Агентство</span>

    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            
            <span class="me-3">
                Привет, <?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_role']) ?>
            </span>

            <!-- КНОПКА ЛК -->
            <a href="profile.php" class="btn btn-outline-primary btn-sm">Личный кабинет</a>

            <?php if ($user_role === 'admin'): ?>
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

    <!-- ПОИСК -->
    <div class="card mb-4 p-3 bg-light">
        <form method="GET" action="index.php" class="row g-3">
            <div class="col-md-8">
                <input 
                    type="text" 
                    name="q" 
                    class="form-control" 
                    placeholder="Поиск по должности..." 
                    value="<?= htmlspecialchars($q) ?>"
                >
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
                    <img src="<?= htmlspecialchars($photo) ?>" class="card-img-top" style="height:200px;object-fit:cover;">

                    <div class="card-body">
                        <h5><?= htmlspecialchars($candidate['full_name']) ?></h5>

                        <!-- ДОЛЖНОСТЬ -->
                        <p class="text-muted fw-semibold">
                            <?= htmlspecialchars($candidate['position']) ?>
                        </p>

                        <!-- PDF -->
                        <?php if (in_array($user_role, ['client', 'admin'])): ?>
                            <?php if (!empty($candidate['resume_pdf'])): ?>
                                <a href="<?= htmlspecialchars($candidate['resume_pdf']) ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100 mb-2">
                                    📄 Открыть резюме
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary w-100 mb-2" disabled>
                                    Резюме отсутствует
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                        <p class="fw-bold text-primary">
                            <?= number_format($candidate['expected_salary'], 0, ',', ' ') ?> ₽
                        </p>

                        <p class="text-muted small">
                            <?= date('d.m.Y', strtotime($candidate['created_at'])) ?>
                        </p>
                    </div>

                    <div class="card-footer bg-white">

                        <?php if ($user_role === 'client'): ?>

                            <?php if (in_array($candidate['id'], $user_applications)): ?>

                                <div class="text-success text-center fw-bold mb-2">
                                    ✔ Вы оставили заявку
                                </div>

                                <form action="delete_my_application.php" method="POST">
                                    <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                                    <button class="btn btn-danger w-100">Удалить заявку</button>
                                </form>

                            <?php else: ?>

                                <form action="make_application.php" method="POST">
                                    <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                                    <button class="btn btn-success w-100">Оставить заявку</button>
                                </form>

                            <?php endif; ?>

                        <?php else: ?>

                            <a href="make_application.php?id=<?= $candidate['id'] ?>" class="btn btn-primary w-100">
                                Подробнее
                            </a>

                        <?php endif; ?>

                    </div>

                </div>
            </div>
        <?php endforeach; ?>

        <?php if (count($candidates) === 0): ?>
            <p class="text-muted">Кандидатов пока нет</p>
        <?php endif; ?>
    </div>

    <!-- ПАГИНАЦИЯ -->
    <ul class="pagination justify-content-center mt-4">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?><?= $q ? '&q='.urlencode($q) : '' ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>
    </ul>

</div>
</body>
</html>