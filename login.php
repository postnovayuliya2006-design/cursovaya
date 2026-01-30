<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Ищем пользователя по email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Проверяем пароль
    if ($user && password_verify($pass, $user['password_hash'])) {
        
        // --- НАЧАЛО ИЗМЕНЕНИЙ ---
        // Запоминаем данные пользователя в сессию
        $_SESSION['user_id'] = $user['id'];
        
        // ВАЖНО: Сохраняем роль! Это наш "браслет"
        $_SESSION['user_role'] = $user['role']; 
        // --- КОНЕЦ ИЗМЕНЕНИЙ ---

        // Перенаправляем: Админа в админку, Клиента в профиль
        if ($user['role'] === 'admin') {
            header("Location: admin_panel.php");
        } else {
            header("Location: index.php"); // Или profile.php
        }
        exit;
    } else {
        echo "Неверный логин или пароль";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Вход в систему</h4>
                </div>
                <div class="card-body">

                    <!-- Сообщения -->
                    <?php if ($errorMsg): ?>
                        <div class="alert alert-danger"><?= $errorMsg ?></div>
                    <?php endif; ?>

                    <?php if ($successMsg): ?>
                        <div class="alert alert-success"><?= $successMsg ?></div>
                    <?php endif; ?>

                    <!-- Форма входа -->
                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label">Email адрес</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            Войти
                        </button>
                    </form>

                    <div class="mt-3 text-center">
                        <a href="register.php">Нет аккаунта? Зарегистрироваться</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>