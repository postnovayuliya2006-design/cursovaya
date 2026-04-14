<?php
session_start();
require '../db.php';
require 'check_admin.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) die("Неверный ID");

$stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$id]);
$candidate = $stmt->fetch();

if (!$candidate) die("Кандидат не найден");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name']);
    $position = trim($_POST['position']);
    $expected_salary = $_POST['expected_salary'];

    $photo_path = $candidate['photo_url'];
    $resume_path = $candidate['resume_pdf'];

    // === ФОТО ===
    if (!empty($_FILES['photo']['name'])) {

        $uploadDir = 'uploads/';
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $newName = uniqid('photo_') . '.' . $ext;

        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newName);

        $photo_path = $uploadDir . $newName;
    }

    // === PDF ===
    if (!empty($_FILES['resume']['name'])) {

        $uploadDir = 'uploads/';
        $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));

        if ($ext !== 'pdf') die("Только PDF");

        $newName = uniqid('resume_') . '.pdf';

        move_uploaded_file($_FILES['resume']['tmp_name'], $uploadDir . $newName);

        $resume_path = $uploadDir . $newName;
    }

    $update = $pdo->prepare("
        UPDATE candidates SET
            full_name = :fn,
            position = :pos,
            expected_salary = :es,
            photo_url = :pu,
            resume_pdf = :rp
        WHERE id = :id
    ");

    $update->execute([
        ':fn' => $full_name,
        ':pos' => $position,
        ':es' => $expected_salary,
        ':pu' => $photo_path,
        ':rp' => $resume_path,
        ':id' => $id
    ]);

    $message = "Обновлено!";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Редактирование</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
    <a href="profile.php" class="btn btn-secondary mb-3">← Назад</a>
<h2>Редактирование</h2>

<?php if ($message): ?>
<div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="card p-4">

<input type="text" name="full_name" value="<?= h($candidate['full_name']) ?>" class="form-control mb-3">

<input type="text" name="position" value="<?= h($candidate['position']) ?>" class="form-control mb-3">

<input type="number" name="expected_salary" value="<?= h($candidate['expected_salary']) ?>" class="form-control mb-3">

<label>Новое фото</label>
<input type="file" name="photo" class="form-control mb-3">

<label>Новое резюме</label>
<input type="file" name="resume" class="form-control mb-3">

<button class="btn btn-success">Сохранить</button>

</form>
</div>
</body>
</html>