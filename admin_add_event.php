<?php
require 'includes/db.php';
require_once 'includes/security.php';
require_once 'includes/functions.php';

if ($_SESSION['user_role'] !== 'admin') { header("Location: index.php"); exit; }

$event = null; 
$options = getFormOptions($pdo);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Нова подія</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <h1>Додати подію</h1>
        <a href="admin.php" class="btn" style="background: #ccc; margin-bottom: 20px;">Назад</a>
        <?php include 'templates/event_form.php'; ?>
    </div>
</body>
</html>