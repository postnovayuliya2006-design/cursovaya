<?php
require '../db.php';

$token = $_GET['token'] ?? '';
$message = '';

if (!$token) die("Неверная ссылка");

$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) die("Токен недействителен");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pass = $_POST['password'];
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE users 
        SET password_hash = ?, reset_token = NULL 
        WHERE id = ?
    ");

    $stmt->execute([$hash, $user['id']]);

    $message = "Пароль изменён! <a href='login.php'>Войти</a>";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Новый пароль</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Создание нового пароля</h4>
                </div>

                <div class="card-body">

                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label">Новый пароль</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100">
                            Сменить пароль
                        </button>

                    </form>

                    <div class="mt-3 text-center">
                        <a href="login.php">Вернуться ко входу</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>