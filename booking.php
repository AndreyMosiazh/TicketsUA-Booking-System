<?php 
require 'includes/db.php'; 
require_once 'includes/security.php';

$event_id = $_GET['event_id'] ?? 0;

// ==========================================
// 1. Інформація про подію
// ==========================================
$stmt = $pdo->prepare("
    SELECT e.*, v.name as venue_name, v.address, a.name as artist_name 
    FROM events e 
    JOIN venues v ON e.venue_id = v.venue_id 
    LEFT JOIN artists a ON e.artist_id = a.artist_id
    WHERE e.event_id = ?
");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) die("Подію не знайдено");

// ==========================================
// 2. Отримуємо ЦІНИ + КОЛЬОРИ (Виправлено)
// ==========================================
$price_stmt = $pdo->prepare("
    SELECT ep.seat_type_id, ep.price, st.name as type_name, st.color_code 
    FROM event_prices ep
    JOIN seat_types st ON ep.seat_type_id = st.type_id
    WHERE ep.event_id = ?
");
$price_stmt->execute([$event_id]);
$price_rows = $price_stmt->fetchAll();

// Формуємо масив $prices для швидкого пошуку ціни по ID типу (id => ціна)
$prices = [];
foreach ($price_rows as $row) {
    $prices[$row['seat_type_id']] = $row['price'];
}

// ==========================================
// 3. Отримуємо МІСЦЯ (Виправлено виконання запиту)
// ==========================================
$sql = "
    SELECT s.*, st.name as type_name, st.color_code,
           (SELECT COUNT(*) FROM tickets t 
            WHERE t.event_id = ? AND t.seat_id = s.seat_id AND t.status IN ('valid', 'used')) as is_taken
    FROM seats s
    JOIN seat_types st ON s.type_id = st.type_id
    WHERE s.venue_id = ?
    ORDER BY s.seat_row, s.seat_number
";

$seats_stmt = $pdo->prepare($sql);
$seats_stmt->execute([$event_id, $event['venue_id']]);
$seats = $seats_stmt->fetchAll();

// Групуємо по рядах
$rows = [];
foreach ($seats as $seat) {
    $rows[$seat['seat_row']][] = $seat;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Бронювання: <?= htmlspecialchars($event['title']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container booking-container">
        <div class="booking-header">
            <h2><?= htmlspecialchars($event['artist_name'] ? $event['artist_name'] . ' - ' : '') . htmlspecialchars($event['title']) ?></h2>
            <p><?= htmlspecialchars($event['venue_name']) ?>, <?= date('d.m.Y H:i', strtotime($event['start_time'])) ?></p>
        </div>
        
        <div class="screen">СЦЕНА</div>
        
        <form action="process_order.php" method="POST" id="bookingForm">
            <?= csrfField() ?>

            <input type="hidden" name="event_id" value="<?= $event_id ?>">
            
            <div class="seats-container">
                <?php foreach ($rows as $row_num => $seats_in_row): ?>
                    <div class="row">
                        <div class="row-label">Ряд <?= $row_num ?></div>
                        
                        <?php foreach ($seats_in_row as $seat): ?>
                            <?php 
                                $isTaken = $seat['is_taken'];
                                // Беремо ціну з масиву. Якщо ціни немає — 0
                                $price = $prices[$seat['type_id']] ?? 0;
                                // Місце можна купити, якщо воно не зайняте І має ціну
                                $canBuy = !$isTaken && $price > 0;
                            ?>
                            
                            <div class="seat-wrapper">
                                <input type="checkbox" 
                                       name="seats[]" 
                                       value="<?= $seat['seat_id'] ?>" 
                                       id="seat-<?= $seat['seat_id'] ?>" 
                                       class="seat-checkbox"
                                       data-price="<?= $price ?>"
                                       <?= $canBuy ? '' : 'disabled' ?>>
                                
                                <label for="seat-<?= $seat['seat_id'] ?>" 
                                       class="seat-label <?= $isTaken ? 'taken' : '' ?>"
                                       style="background-color: <?= $isTaken ? '#ccc' : $seat['color_code'] ?>; <?= $canBuy ? '' : 'opacity: 0.5; cursor: not-allowed;' ?>"
                                       title="<?= htmlspecialchars($seat['type_name']) ?>: <?= $price ?> грн (Місце <?= $seat['seat_number'] ?>)">
                                    <small><?= $seat['seat_number'] ?></small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="legend" style="margin-top: 20px; text-align: center;">
                <?php foreach ($price_rows as $p): ?>
                    <span style="margin: 0 10px; display: inline-flex; align-items: center;">
                        <span style="display:inline-block; width:15px; height:15px; background:<?= $p['color_code'] ?>; margin-right: 5px; border-radius: 3px;"></span>
                        <?= htmlspecialchars($p['type_name']) ?>: <strong><?= number_format($p['price'], 0) ?> грн</strong>
                    </span>
                <?php endforeach; ?>
            </div>

            <div class="summary-bar" id="summaryBar">
                <div class="summary-content">
                    <div>Обрано: <span id="count">0</span></div>
                    <div>Сума: <span id="total">0</span> грн</div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="submit" class="btn btn-buy">Оформити замовлення</button>
                    <?php else: ?>
                        <a href="login.php" class="btn" style="background: #fff; color: #333;">Увійдіть для покупки</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <script src="js/script.js"></script>
</body>
</html>