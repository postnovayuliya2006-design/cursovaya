<?php
session_start();
require '../db.php';

$error = '';
$step = 1; // 1 = email, 2 = новый пароль

$email = $_SESSION['reset_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ================= STEP 1: EMAIL =================
    if (isset($_POST['email'])) {

        $email = trim($_POST['email']);

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_user_id'] = $user['id'];
            $step = 2;
        } else {
            $error = "Пользователь с таким email не найден";
        }
    }

    // ================= STEP 2: PASSWORD =================
    if (isset($_POST['password']) && isset($_POST['password_confirm'])) {

        $pass = $_POST['password'];
        $confirm = $_POST['password_confirm'];

        if ($pass !== $confirm) {
            $error = "Пароли не совпадают";
            $step = 2;
        } else {

            $hash = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $_SESSION['reset_user_id']]);

            // очистка
            unset($_SESSION['reset_email'], $_SESSION['reset_user_id']);

            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Восстановление пароля</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Восстановление пароля</h4>
                </div>

                <div class="card-body">

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <!-- ================= STEP 1 ================= -->
                    <?php if ($step === 1): ?>

                        <form method="POST">

                            <div class="mb-3">
                                <label class="form-label">Введите email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <button class="btn btn-warning w-100">
                                Далее
                            </button>

                        </form>

                    <!-- ================= STEP 2 ================= -->
                    <?php else: ?>

                        <form method="POST">

                            <div class="mb-3">
                                <label class="form-label">Новый пароль</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Подтверждение пароля</label>
                                <input type="password" name="password_confirm" class="form-control" required>
                            </div>

                            <button class="btn btn-primary w-100">
                                Сменить пароль
                            </button>

                        </form>

                    <?php endif; ?>

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