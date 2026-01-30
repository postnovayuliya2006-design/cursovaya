<?php
require 'check_admin.php'; // Только админ!
require '../db.php';

$sql = "
    SELECT 
        applications.id as application_id,
        applications.created_at,
        applications.status,
        users.email as employer_email,
        users.username as employer_name,
        candidates.full_name as candidate_name,
        candidates.expected_salary
    FROM applications
    JOIN users ON applications.employer_id = users.id
    JOIN candidates ON applications.candidate_id = candidates.id
    ORDER BY applications.id DESC
";

$stmt = $pdo->query($sql);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Управление заявками</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h1>Все заявки работодателей</h1>
    <a href="index.php">На главную</a>
    
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID Заявки</th>
                <th>Дата</th>
                <th>Статус</th>
                <th>Работодатель</th>
                <th>Кандидат</th>
                <th>Ожидаемая зарплата</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($applications as $app): ?>
            <tr>
                <td>#<?= $app['application_id'] ?></td>
                <td><?= date('d.m.Y H:i', strtotime($app['created_at'])) ?></td>
                <td>
                    <span class="badge bg-<?= 
                        $app['status'] == 'new' ? 'info' : 
                        ($app['status'] == 'processing' ? 'warning' : 'success') 
                    ?>">
                        <?= $app['status'] ?>
                    </span>
                </td>
                <td>
                    <?= htmlspecialchars($app['employer_name']) ?><br>
                    <small><?= htmlspecialchars($app['employer_email']) ?></small>
                </td>
                <td><?= htmlspecialchars($app['candidate_name']) ?></td>
                <td><?= number_format($app['expected_salary'], 0, ',', ' ') ?> ₽</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>