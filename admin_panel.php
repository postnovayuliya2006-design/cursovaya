<?php
// Самая первая строчка — вызов охраны!
require 'check_admin.php'; 
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админка - Кадровое Агентство</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <div class="container">
        <div class="alert alert-success">
            <h1>Панель Администратора</h1>
            <p>Добро пожаловать, <?= $_SESSION['user_name'] ?? 'Администратор' ?>!</p>
            <p>Кадровое агентство - управление базой кандидатов</p>
        </div>
        
        <a href="index.php" class="btn btn-danger">Выйти</a>
        <a href="add_item.php" class="btn btn-success btn-sm">+ Добавить нового кандидата</a>
        <a href="index.php" class="btn btn-primary mt-2">Просмотр базы</a>
    </div>
</body>
</html>