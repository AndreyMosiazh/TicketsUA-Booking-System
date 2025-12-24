<?php
require 'includes/db.php';

$event_id = $_GET['id'] ?? 0;

// Отримуємо повну інфу: Подія + Локація + Артист + Категорія
$stmt = $pdo->prepare("
    SELECT e.*, 
           v.name as venue_name, v.address, v.city_id, 
           c.name as city_name,
           a.name as artist_name, a.bio as artist_bio, a.image_url as artist_image,
           cat.name as category_name
    FROM events e
    JOIN venues v ON e.venue_id = v.venue_id
    JOIN cities c ON v.city_id = c.city_id
    LEFT JOIN artists a ON e.artist_id = a.artist_id
    JOIN event_categories cat ON e.category_id = cat.category_id
    WHERE e.event_id = ?
");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) die("Подію не знайдено.");

// Отримуємо ціни
$prices_stmt = $pdo->prepare("
    SELECT st.name, ep.price, st.color_code 
    FROM event_prices ep
    JOIN seat_types st ON ep.seat_type_id = st.type_id
    WHERE ep.event_id = ?
    ORDER BY ep.price ASC
");
$prices_stmt->execute([$event_id]);
$prices = $prices_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($event['title']) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Специфічні стилі для сторінки події */
        .event-header { display: flex; gap: 30px; margin-bottom: 40px; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .poster-large { width: 300px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .event-info { flex: 1; }
        .price-tag { display: inline-block; padding: 5px 10px; border-radius: 5px; color: white; margin-right: 5px; font-size: 0.9em; }
        .artist-block { margin-top: 30px; display: flex; align-items: center; gap: 20px; background: #fff; padding: 20px; border-radius: 10px; }
        .artist-avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="event-header">
            <img src="<?= $event['poster_url'] ?>" alt="Poster" class="poster-large">
            
            <div class="event-info">
                <span class="badge"><?= $event['category_name'] ?></span>
                <h1><?= htmlspecialchars($event['artist_name'] ? $event['artist_name'] . ': ' : '') . htmlspecialchars($event['title']) ?></h1>
                
                <p class="location">
                    <?= htmlspecialchars($event['city_name']) ?>, <?= htmlspecialchars($event['venue_name']) ?><br>
                    <small><?= htmlspecialchars($event['address']) ?></small>
                </p>

                <p class="date"><?= date('d.m.Y, H:i', strtotime($event['start_time'])) ?></p>
                
                <div style="margin: 20px 0;">
                    <h3>Ціни:</h3>
                    <?php foreach($prices as $price): ?>
                        <span class="price-tag" style="background: <?= $price['color_code'] ?>">
                            <?= $price['name'] ?>: <?= number_format($price['price'], 0) ?> грн
                        </span>
                    <?php endforeach; ?>
                </div>

                <a href="booking.php?event_id=<?= $event['event_id'] ?>" class="btn btn-buy" style="padding: 15px 40px; font-size: 1.2em;">🎫 Купити квиток</a>
            </div>
        </div>

        <div class="description">
            <h2>Про подію</h2>
            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
        </div>

        <?php if($event['artist_id']): ?>
        <div class="artist-block">
            <img src="<?= $event['artist_image'] ?>" alt="<?= $event['artist_name'] ?>" class="artist-avatar">
            <div>
                <h3>Про артиста: <?= htmlspecialchars($event['artist_name']) ?></h3>
                <p><?= nl2br(htmlspecialchars($event['artist_bio'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    </div> </div> <?php include 'includes/footer.php'; ?> </body>
</body>
</html>