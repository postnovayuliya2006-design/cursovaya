<?php
// admin_seeder.php
session_start();
require '../db.php';
require 'check_admin.php'; // Доступ только админам!

$message = "";

// Получаем список всех таблиц в базе
$tables = [];
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tableName = $_POST['table_name'];
    $count = (int)$_POST['count'];
    
    // Защита: проверяем, есть ли такая таблица в нашем белом списке
    if (!in_array($tableName, $tables)) {
        die("Ошибка: Таблица не найдена.");
    }

    // --- ЭТАП 1: ЭКСПОРТ В CSV (БЭКАП) ---
    $exportDir = dirname(__DIR__) . '/exports/'; // Папка exports в корне сайта
    if (!is_dir($exportDir)) mkdir($exportDir, 0777, true);
    
    $filename = $exportDir . $tableName . '_' . date('Y-m-d_H-i-s') . '.csv';
    $fp = fopen($filename, 'w');
    
    // Получаем все данные
    $stmt = $pdo->query("SELECT * FROM `$tableName`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        $message = "Таблица пуста! Сначала создайте хотя бы одну запись вручную.";
    } else {
        // Записываем заголовки (названия колонок)
        fputcsv($fp, array_keys($rows[0]));
        
        // Записываем данные
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        $message .= "Бэкап сохранен: $filename<br>";

        // --- ЭТАП 2: ГЕНЕРАЦИЯ (КЛОНИРОВАНИЕ) ---
        // Берем одну случайную строку как шаблон
        $template = $rows[array_rand($rows)];
        
        $inserted = 0;
        for ($i = 0; $i < $count; $i++) {
            $newRow = [];
            $cols = [];
            $vals = [];
            
            foreach ($template as $key => $value) {
                // Пропускаем ID (он автоинкрементный)
                if ($key === 'id') continue;
                
                // ЛОГИКА РАНДОМИЗАЦИИ
                if (is_numeric($value)) {
                    $percent = mt_rand(-15, 15) / 100; 
                    $newValue = $value * (1 + $percent);
                    
                    // Если поле похоже на ID внешнего ключа — не трогаем
                    if (strpos($key, 'id') !== false) {
                         $newValue = $value; 
                    } else {
                         $newValue = round($newValue, 2);
                    }
                } else {
                    // Строка: добавляем случайный хвост, чтобы обойти UNIQUE (например, email)
                    $newValue = $value . "_" . mt_rand(1000, 9999);
                }
                
                $cols[] = "`$key`";
                $vals[] = $pdo->quote($newValue);
            }
            
            // Собираем SQL INSERT
            $sql = "INSERT INTO `$tableName` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
            
            try {
                $pdo->exec($sql);
                $inserted++;
            } catch (Exception $e) {
                // Игнорируем ошибки, идем дальше
                continue;
            }
        }
        $message .= "Успешно сгенерировано строк: $inserted из $count.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Генератор данных</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5 bg-light">
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3>⚙️ Генератор контента (Seeder)</h3>
            </div>
            <div class="card-body">
                
                <?php if ($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label>Выберите таблицу для наполнения:</label>
                        <select name="table_name" class="form-select">
                            <?php foreach ($tables as $t): ?>
                                <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Рекомендуется выбирать products, candidates или applications.</small>
                    </div>

                    <div class="mb-3">
                        <label>Сколько записей добавить?</label>
                        <input type="number" name="count" class="form-control" value="10" min="1" max="1000">
                    </div>

                    <div class="alert alert-warning">
                        <small>
                            ⚠️ <strong>Внимание:</strong> Скрипт создаст CSV-бэкап в папке /exports в корне сайта, а затем скопирует случайную запись указанное количество раз, изменяя числовые значения на ±15%.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success w-100">🚀 Наполнить и Бэкапить</button>
                </form>
                
                <a href="index.php" class="btn btn-secondary mt-3">← Вернуться на сайт</a>
            </div>
        </div>
    </div>
</body>
</html>
