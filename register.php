<?php
require 'includes/db.php';
require_once 'includes/security.php'; 

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf(); 

    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $error = "Цей email вже зареєстрований!";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$first_name, $last_name, $email, $password_hash])) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $error = "Сталася помилка реєстрації.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Реєстрація</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 40px auto;">
            <h2>Реєстрація</h2>
            <?php if($error): ?><p style="color: red"><?= $error ?></p><?php endif; ?>
            
            <form method="POST">
                <?= csrfField() ?>

                <input type="text" name="first_name" placeholder="Ім'я" required style="width: 100%; margin-bottom: 10px; padding: 8px;">
                <input type="text" name="last_name" placeholder="Прізвище" required style="width: 100%; margin-bottom: 10px; padding: 8px;">
                <input type="email" name="email" placeholder="Email" required style="width: 100%; margin-bottom: 10px; padding: 8px;">
                <input type="password" name="password" placeholder="Пароль" required style="width: 100%; margin-bottom: 10px; padding: 8px;">
                <button type="submit" class="btn">Зареєструватися</button>
            </form>
        </div>
    </div>
    
    </div> </div> <?php include 'includes/footer.php'; ?> </body>
</body>
</html>