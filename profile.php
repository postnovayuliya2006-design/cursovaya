<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'guest';

// --- ЕСЛИ АДМИН ---
if ($user_role === 'admin') {

    $stmt = $pdo->query("SELECT * FROM candidates ORDER BY id DESC");
    $candidates = $stmt->fetchAll();

} else {
    // --- ЕСЛИ КЛИЕНТ ---
    $sql = "
        SELECT 
            applications.id as application_id, 
            applications.candidate_id,
            applications.created_at, 
            applications.status, 
            candidates.full_name, 
            candidates.expected_salary,
            candidates.photo_url,
            candidates.resume_pdf
        FROM applications 
        JOIN candidates ON applications.candidate_id = candidates.id 
        WHERE applications.user_id = ? 
        ORDER BY applications.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $my_applications = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Личный кабинет</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <span class="navbar-brand">Личный кабинет</span>
        <div>
            <a href="index.php" class="btn btn-outline-light btn-sm">На главную</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
        </div>
    </div>
</nav>

<div class="container">

<?php if ($user_role === 'admin'): ?>

    <!-- ================= ADMIN ================= -->
    <h2>Все кандидаты</h2>

    <div class="row">
        <?php foreach ($candidates as $candidate): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">

                    <?php $photo = $candidate['photo_url'] ?: 'https://via.placeholder.com/300'; ?>
                    <img src="<?= htmlspecialchars($photo) ?>" class="card-img-top" style="height:200px; object-fit:cover;">

                    <div class="card-body">
                        <h5><?= htmlspecialchars($candidate['full_name']) ?></h5>
                        <p><?= htmlspecialchars($candidate['position']) ?></p>
                        <p class="fw-bold"><?= $candidate['expected_salary'] ?> ₽</p>
                        
                        <!-- Кнопка резюме -->
<?php if (!empty($candidate['resume_pdf'])): ?>
    <a href="<?= htmlspecialchars($candidate['resume_pdf']) ?>" 
       target="_blank" 
       class="btn btn-outline-primary w-100 mb-2">
       📄 Открыть резюме
    </a>
<?php else: ?>
    <button class="btn btn-secondary w-100 mb-2" disabled>
        Резюме отсутствует
    </button>
<?php endif; ?>
                        
                    </div>

                    <div class="card-footer d-flex gap-2">

                        <!-- РЕДАКТИРОВАТЬ -->
                        <a href="edit_candidate.php?id=<?= $candidate['id'] ?>" 
                           class="btn btn-warning w-50">
                           ✏️ Редактировать
                        </a>

                        <!-- УДАЛИТЬ -->
                        <form action="delete_candidate.php" method="POST" class="w-50">
                            <input type="hidden" name="id" value="<?= $candidate['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button class="btn btn-danger w-100">🗑️ Удалить</button>
                        </form>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>

    <!-- ================= CLIENT ================= -->
    <h2>Мои заявки</h2>

    <?php if (count($my_applications) > 0): ?>

        <div class="row">
            <?php foreach ($my_applications as $application): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">

                        <img src="<?= htmlspecialchars($application['photo_url'] ?: 'https://via.placeholder.com/300') ?>" 
                             class="card-img-top" style="height:200px; object-fit:cover;">

                        <div class="card-body">
                            <h5><?= htmlspecialchars($application['full_name']) ?></h5>

                            <a href="<?= htmlspecialchars($application['resume_pdf']) ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-secondary mb-2">
                                📄 Резюме
                            </a>

                            <p class="fw-bold"><?= $application['expected_salary'] ?> ₽</p>
                            <p class="text-muted"><?= $application['created_at'] ?></p>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <p>У вас нет заявок</p>
    <?php endif; ?>

<?php endif; ?>

</div>
</body>
</html>