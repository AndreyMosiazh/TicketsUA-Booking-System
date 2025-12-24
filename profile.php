<?php
require 'includes/db.php';

// Захист: якщо не залогінений — кидаємо на логін
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Отримуємо історію замовлень
// Це складний запит, який збирає дані з bookings, tickets, events
// profile.php

$sql = "
    SELECT b.booking_id, b.total_amount, b.booking_date, b.status,
           MAX(e.title) as event_title, 
           MAX(e.start_time) as start_time,
           MAX(v.name) as venue_name, -- Додали назву залу
           COUNT(t.ticket_id) as tickets_count,
           -- Отримати список місць через кому (Наприклад: Ряд 1 Місце 5, Ряд 1 Місце 6)
           GROUP_CONCAT(
               CONCAT('Ряд ', s.seat_row, ' (Місце ', s.seat_number, ')') 
               SEPARATOR ', '
           ) as seat_details,
           -- Отримати коди квитків для посилання
           GROUP_CONCAT(t.unique_code SEPARATOR ',') as ticket_codes
    FROM bookings b
    JOIN tickets t ON b.booking_id = t.booking_id
    JOIN seats s ON t.seat_id = s.seat_id  -- Приєднуємо таблицю місць!
    JOIN events e ON t.event_id = e.event_id
    JOIN venues v ON e.venue_id = v.venue_id
    WHERE b.user_id = ?
    GROUP BY b.booking_id
    ORDER BY b.booking_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$my_bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Мій кабінет</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Особистий кабінет</h1>
        <p>Ім'я: <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></p>
        
        <h2>Історія замовлень</h2>
        
        <?php if (count($my_bookings) > 0): ?>
            <?php foreach ($my_bookings as $booking): ?>
                <div class="card">
                    <div style="display: flex; justify-content: space-between;">
                        <h3><?= htmlspecialchars($booking['event_title']) ?></h3>
                        <span class="price"><?= number_format($booking['total_amount'], 0) ?> грн</span>
                    </div>
                    
                    <p><strong>Де:</strong> <?= htmlspecialchars($booking['venue_name']) ?></p>
                    <p><strong>Коли:</strong> <?= date('d.m.Y H:i', strtotime($booking['start_time'])) ?></p>
                    
                    <hr style="margin: 10px 0; border: 0; border-top: 1px solid #eee;">
                    
                    <p><strong>Ваші місця:</strong><br>
                    <span style="color: #0056b3; font-weight: bold;">
                        <?= htmlspecialchars($booking['seat_details']) ?>
                    </span>
                    </p>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                        <div>
                            <small>Статус: 
                                <span style="
                                    color: <?= $booking['status'] == 'paid' ? 'green' : ($booking['status'] == 'cancelled' ? 'red' : 'orange') ?>; 
                                    font-weight: bold;">
                                    <?= strtoupper($booking['status']) ?>
                                </span>
                            </small>
                            <br>
                            <small style="color: #aaa;">Замовлення #<?= $booking['booking_id'] ?></small>
                        </div>
                        
                        <?php if($booking['status'] == 'paid'): ?>
                            <?php 
                                // Беремо перший код квитка для прикладу
                                $codes = explode(',', $booking['ticket_codes']);
                                $first_code = $codes[0] ?? '';
                            ?>
                            <a href="check_ticket.php?code=<?= $first_code ?>" class="btn" style="background: #6f42c1; color: white; padding: 5px 15px; font-size: 12px;">
                                Показати QR
                            </a>
                        <?php endif; ?>
                        </div>
                    </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>У вас поки немає замовлень.</p>
        <?php endif; ?>
    </div>
    
    </div> </div> <?php include 'includes/footer.php'; ?> </body>
</body>
</html>