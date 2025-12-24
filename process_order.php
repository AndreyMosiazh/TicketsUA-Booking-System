<?php
require 'includes/db.php';
require 'includes/security.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf(); 

    if (empty($_POST['seats']) || !isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $event_id = (int)$_POST['event_id'];
    $seat_ids = $_POST['seats']; 

    try {
        $pdo->beginTransaction();

        $placeholders = str_repeat('?,', count($seat_ids) - 1) . '?';
        
        $check_sql = "
            SELECT seat_id FROM tickets 
            WHERE event_id = ? 
            AND seat_id IN ($placeholders) 
            AND status IN ('valid', 'used')
            FOR UPDATE
        ";
        
        $params = array_merge([$event_id], $seat_ids);
        $stmt_check = $pdo->prepare($check_sql);
        $stmt_check->execute($params);
        
        if ($stmt_check->rowCount() > 0) {
            throw new Exception("На жаль, одне з обраних місць вже було придбано іншим користувачем секунду тому.");
        }

        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, total_amount, status) VALUES (?, 0, 'pending')");
        $stmt->execute([$user_id]);
        $booking_id = $pdo->lastInsertId();

        $total_amount = 0;

        $price_sql = "SELECT ep.price FROM seats s JOIN event_prices ep ON s.type_id = ep.seat_type_id WHERE s.seat_id = ? AND ep.event_id = ?";
        $price_stmt = $pdo->prepare($price_sql);

        $ticket_stmt = $pdo->prepare("INSERT INTO tickets (booking_id, event_id, seat_id, price_at_purchase, unique_code) VALUES (?, ?, ?, ?, UUID())");

        foreach ($seat_ids as $seat_id) {
            $price_stmt->execute([$seat_id, $event_id]);
            $price = $price_stmt->fetchColumn();
            
            if (!$price) throw new Exception("Помилка ціни для місця #$seat_id");

            $total_amount += $price;
            $ticket_stmt->execute([$booking_id, $event_id, $seat_id, $price]);
        }

        $pdo->prepare("UPDATE bookings SET total_amount = ?, status = 'paid' WHERE booking_id = ?")
            ->execute([$total_amount, $booking_id]);

        $pdo->commit();
        
        header("Location: success.php?booking_id=$booking_id");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Помилка замовлення: " . $e->getMessage() . " <a href='booking.php?event_id=$event_id'>Спробувати ще раз</a>");
    }
} else {
    header("Location: index.php");
}