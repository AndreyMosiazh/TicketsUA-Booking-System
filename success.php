<?php require 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Успішне замовлення</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-box { text-align: center; padding: 50px; background: white; border-radius: 15px; margin-top: 50px; }
        .icon { font-size: 80px; color: #28a745; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <div class="success-box">
            <div class="icon"></div>
            <h1>Дякуємо за покупку!</h1>
            <p>Ваше замовлення <strong>#<?= htmlspecialchars($_GET['booking_id'] ?? '') ?></strong> успішно оформлено.</p>
            <p>Квитки вже у вашому особистому кабінеті.</p>
            
            <div style="margin-top: 30px;">
                <a href="profile.php" class="btn btn-buy">Перейти до квитків</a>
                <a href="index.php" class="btn" style="background: #eee; color: #333; margin-left: 10px;">На головну</a>
            </div>
        </div>
    </div>
    
    </div> </div> <?php include 'includes/footer.php'; ?> </body>
</body>
</html>