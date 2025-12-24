<?php
require 'includes/db.php';

$code = $_GET['code'] ?? '';
$ticket = null;
$message = '';
$status_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'validate') {
    // Дія "Погасити квиток" (Пропустити людину)
    $code = $_POST['code'];
    $stmt = $pdo->prepare("UPDATE tickets SET status = 'used' WHERE unique_code = ? AND status = 'valid'");
    $stmt->execute([$code]);
    if ($stmt->rowCount() > 0) {
        $message = "Квиток успішно активовано! Вхід дозволено.";
        $status_class = "success";
    } else {
        $message = "Помилка! Квиток вже використаний або недійсний.";
        $status_class = "error";
    }
}

// Пошук квитка
if ($code) {
    $sql = "
        SELECT t.*, e.title, e.start_time, v.name as venue_name, 
               s.seat_row, s.seat_number, st.name as seat_type
        FROM tickets t
        JOIN events e ON t.event_id = e.event_id
        JOIN venues v ON e.venue_id = v.venue_id
        JOIN seats s ON t.seat_id = s.seat_id
        JOIN seat_types st ON s.type_id = st.type_id
        WHERE t.unique_code = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$code]);
    $ticket = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Перевірка квитка</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .scanner-box { max-width: 500px; margin: 40px auto; text-align: center; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .status-badge { display: inline-block; padding: 10px 20px; border-radius: 50px; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; color: white; }
        .valid { background: #28a745; box-shadow: 0 0 20px rgba(40, 167, 69, 0.5); }
        .used { background: #ffc107; color: #333; }
        .invalid { background: #dc3545; }
        
        .ticket-details { text-align: left; background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0; border: 1px dashed #ccc; }
        .ticket-details p { margin: 5px 0; font-size: 1.1em; }
        .label { color: #777; font-size: 0.9em; }

        .big-input { width: 100%; padding: 15px; font-size: 1.2em; text-align: center; border: 2px solid #eee; border-radius: 10px; margin-bottom: 10px; }
        .qr-placeholder { width: 150px; height: 150px; background: #333; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; color: white; border-radius: 10px; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="scanner-box">
            <h1>Сканер квитків</h1>
            
            <?php if ($message): ?>
                <div style="padding: 15px; margin-bottom: 20px; border-radius: 5px; color: white; background: <?= $status_class == 'success' ? '#28a745' : '#dc3545' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="GET">
                <input type="text" name="code" class="big-input" placeholder="Введіть код квитка" value="<?= htmlspecialchars($code) ?>">
                <button type="submit" class="btn btn-buy" style="width: 100%;">Пошук</button>
            </form>

            <?php if ($code && !$ticket): ?>
                <h3 style="color: red; margin-top: 20px;">Квиток не знайдено!</h3>
            <?php endif; ?>

            <?php if ($ticket): ?>
                <hr>
                
                <?php if ($ticket['status'] === 'valid'): ?>
                    <div class="status-badge valid">ДІЙСНИЙ</div>
                <?php elseif ($ticket['status'] === 'used'): ?>
                    <div class="status-badge used">ВЖЕ ВИКОРИСТАНО</div>
                <?php else: ?>
                    <div class="status-badge invalid">СКАСОВАНО</div>
                <?php endif; ?>

                <div class="ticket-details">
    
    <?php if ($ticket): ?>
        <?php 
            // 1. Формуємо текст
            $qrRaw = "TICKETSUA\n";
            $qrRaw .= "----------------\n";
            $qrRaw .= "Подія: " . $ticket['title'] . "\n";
            $qrRaw .= "Дата: " . date('d.m H:i', strtotime($ticket['start_time'])) . "\n";
            $qrRaw .= "Зал: " . $ticket['venue_name'] . "\n";
            $qrRaw .= "Ряд: " . $ticket['seat_row'] . " Місце: " . $ticket['seat_number'] . "\n";
            $qrRaw .= "----------------\n";
            $qrRaw .= "Код: " . $ticket['unique_code'];

            // 2. Кодуємо текст для URL (це збереже українські літери)
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&charset-source=UTF-8&data=" . urlencode($qrRaw);
        ?>
        
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="<?= $qrUrl ?>" alt="QR Code" style="border: 2px solid #eee; padding: 5px; border-radius: 5px;">
        </div>
    <?php endif; ?>
    <p><span class="label">Подія:</span><br><strong><?= htmlspecialchars($ticket['title']) ?></strong></p>
    <p><span class="label">Дата:</span> <strong><?= date('d.m.Y H:i', strtotime($ticket['start_time'])) ?></strong></p>


                <?php if ($ticket['status'] === 'valid'): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="validate">
                        <input type="hidden" name="code" value="<?= $ticket['unique_code'] ?>">
                        <button type="submit" class="btn" style="background: #28a745; color: white; width: 100%; padding: 15px; font-size: 1.2em;">
                            ПРОПУСТИТИ (СКАНУВАТИ)
                        </button>
                    </form>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>