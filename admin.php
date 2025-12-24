<?php
require 'includes/db.php';
require_once 'includes/security.php';

// Статистика з View
$stats = $pdo->query("SELECT * FROM view_event_stats ORDER BY start_time DESC")->fetchAll();
// Перевірка прав адміна
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// 1. Користувачі
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

// 2. Бронювання (Оновлений складний запит)
$sql = "
    SELECT b.*, 
           u.email, u.first_name, u.last_name,
           -- Групуємо назви подій в один рядок, якщо в замовленні їх кілька (хоча зазвичай одна)
           GROUP_CONCAT(DISTINCT CONCAT(COALESCE(a.name, ''), ' ', e.title) SEPARATOR ', ') as event_info,
           COUNT(t.ticket_id) as tickets_count
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    LEFT JOIN tickets t ON b.booking_id = t.booking_id
    LEFT JOIN events e ON t.event_id = e.event_id
    LEFT JOIN artists a ON e.artist_id = a.artist_id
    GROUP BY b.booking_id
    ORDER BY b.booking_date DESC
";
$bookings = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Адмін-панель</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background: white; font-size: 14px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background-color: #333; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status-paid { color: green; font-weight: bold; }
        .status-pending { color: orange; }
        .status-cancelled { color: red; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1>Адміністративна панель</h1>

        <div style="margin: 20px 0;">
            <a href="admin_add_event.php" class="btn btn-buy">Додати нову подію</a>

            <a href="index.php" class="btn" style="background: #6c757d; margin-left: 10px;">На головну</a>
            
            <a href="admin_settings.php" class="btn" style="background: #17a2b8; color: white; margin-left: 10px;">Довідники (Артисти/Зали)</a>
        </div>

        <div class="card" style="margin-bottom: 30px;">
            <h2>Усі замовлення</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Клієнт</th>
                        <th>Подія</th>
                        <th>Квитків</th>
                        <th>Сума</th>
                        <th>Статус</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td>#<?= $b['booking_id'] ?></td>
                        <td>
                            <?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?><br>
                            <small><?= htmlspecialchars($b['email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($b['event_info']) ?></td>
                        <td><?= $b['tickets_count'] ?></td>
                        <td><?= $b['total_amount'] ?> грн</td>
                        <td class="status-<?= $b['status'] ?>"><?= strtoupper($b['status']) ?></td>
                        <td><?= $b['booking_date'] ?></td>
                    </tr>
                    <?php endforeach; ?>                    
                </tbody>
            </table>
        </div>

        <div class="card" style="margin-bottom: 30px;">
            <h2>Аналітика продажів (VIEW)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Подія</th>
                        <th>Дата</th>
                        <th>Продано квитків</th>
                        <th>Виручка</th>
                        <th>Заповненість</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['title']) ?></td>
                        <td><?= date('d.m H:i', strtotime($s['start_time'])) ?></td>
                        <td><?= $s['tickets_sold'] ?> / <?= $s['total_seats'] ?></td>
                        <td><strong><?= number_format($s['total_revenue'], 0) ?> грн</strong></td>
                        <td>
                            <div style="background: #eee; width: 100px; height: 10px; border-radius: 5px; overflow: hidden;">
                                <div style="background: #28a745; width: <?= $s['occupancy_rate'] ?>%; height: 100%;"></div>
                            </div>
                            <small><?= $s['occupancy_rate'] ?>%</small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>Керування подіями</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Подія</th>
                        <th>Дата</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Отримуємо всі події
                    $all_events = $pdo->query("SELECT event_id, title, start_time FROM events ORDER BY start_time DESC")->fetchAll();
                    foreach ($all_events as $e): 
                    ?>
                    <tr>
                        <td><?= $e['event_id'] ?></td>
                        <td><?= htmlspecialchars($e['title']) ?></td>
                        <td><?= $e['start_time'] ?></td>
                        <td>
                            <a href="admin_edit_event.php?id=<?= $e['event_id'] ?>" class="btn" style="background: #ffc107; color: black; padding: 5px 10px; font-size: 12px;">Редагувати</a>
                      
                            <a href="actions/delete_event.php?id=<?= $e['event_id'] ?>" 
                            class="btn" 
                            style="background: #dc3545; color: white; padding: 5px 10px; font-size: 12px; margin-left: 5px;"
                            onclick="return confirm('Ви впевнені? Це безповоротно видалить подію та всі замовлені квитки до неї!');">
                            Видалити
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    </div> </div> <?php include 'includes/footer.php'; ?> </body>
</body>
</html>





