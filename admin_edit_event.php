<?php
// admin_edit_event.php
require 'includes/db.php';
require_once 'includes/security.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php"); exit;
}

$id = $_GET['id'] ?? 0;
// Отримуємо подію
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) die("Подію не знайдено");

// Отримуємо поточні ціни події [type_id => price]
$p_stmt = $pdo->prepare("SELECT seat_type_id, price FROM event_prices WHERE event_id = ?");
$p_stmt->execute([$id]);
$current_prices = $p_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Завантажуємо опції для селектів
$options = getFormOptions($pdo);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагування події</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <h1>Редагувати: <?= htmlspecialchars($event['title']) ?></h1>
        <a href="admin.php" class="btn" style="background: #ccc; margin-bottom: 20px;">Назад</a>
        
        <?php include 'templates/event_form.php'; ?>
    </div>
</body>
</html>