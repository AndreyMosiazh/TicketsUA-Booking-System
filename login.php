<?php
require 'includes/db.php';
require_once 'includes/security.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf(); 

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['first_name'];
        $_SESSION['user_role'] = $user['role'];
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Невірний логін або пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Вхід</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 40px auto;">
            <h2>Вхід</h2>
            <?php if(isset($_GET['registered'])): ?>
                <p style="color: green">Реєстрація успішна! Тепер увійдіть.</p>
            <?php endif; ?>
            
            <?php if($error): ?><p style="color: red"><?= $error ?></p><?php endif; ?>
            
            <form method="POST">
                <?= csrfField() ?>

                <input type="email" name="email" placeholder="Email" required style="width: 100%; margin-bottom: 10px; padding: 8px;">
                <input type="password" name="password" placeholder="Пароль" required style="width: 100%; margin-bottom: 10px; padding: 8px;">
                <button type="submit" class="btn">Увійти</button>
            </form>
        </div>
    </div>
    
    </div> </div> <?php include 'includes/footer.php'; ?> </body>
</body>
</html>