<?php require 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>TicketsUA — Афіша подій</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Актуальні події</h1>
        
        <div class="filter-bar" style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="q" placeholder="Пошук події..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="flex: 1; padding: 10px;">
                
                <select name="cat" style="padding: 10px;">
                    <option value="">Усі категорії</option>
                    <?php 
                    $cats = $pdo->query("SELECT * FROM event_categories")->fetchAll();
                    foreach($cats as $c): 
                    ?>
                        <option value="<?= $c['category_id'] ?>" <?= (isset($_GET['cat']) && $_GET['cat'] == $c['category_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn btn-buy">Знайти</button>
            </form>
        </div>

        <div class="event-grid">
           <?php
            // 1. Формуємо умови пошуку (WHERE)
            $where = ["e.status = 'scheduled'"];
            $params = [];

            // Якщо є пошуковий запит
            if (!empty($_GET['q'])) {
                $where[] = "(e.title LIKE ? OR a.name LIKE ?)";
                $params[] = "%" . $_GET['q'] . "%";
                $params[] = "%" . $_GET['q'] . "%";
            }

            // Якщо обрана категорія
            if (!empty($_GET['cat'])) {
                $where[] = "e.category_id = ?";
                $params[] = $_GET['cat'];
            }

            // Об'єднуємо умови
            $where_sql = implode(' AND ', $where);

            // 2. Основний SQL запит
            $sql = "
                SELECT e.*, 
                    v.name as venue_name, 
                    c.name as city_name, 
                    cat.name as category_name, 
                    a.name as artist_name,
                    (SELECT MIN(price) FROM event_prices ep WHERE ep.event_id = e.event_id) as min_price
                FROM events e
                JOIN venues v ON e.venue_id = v.venue_id
                JOIN cities c ON v.city_id = c.city_id
                JOIN event_categories cat ON e.category_id = cat.category_id
                LEFT JOIN artists a ON e.artist_id = a.artist_id
                WHERE $where_sql
                ORDER BY e.start_time ASC
            ";   

            // 3. Виконуємо запит
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // 4. Виводимо результати
            while ($row = $stmt->fetch()) {
                // Якщо немає постера, ставимо заглушку
                $poster = $row['poster_url'] ? $row['poster_url'] : 'https://placehold.co/300x400?text=No+Image';
                // Формуємо заголовок (Артист: Назва або просто Назва)
                $title = $row['artist_name'] ? $row['artist_name'] . ': ' . $row['title'] : $row['title'];
                $date = date('d.m.Y H:i', strtotime($row['start_time']));
                
                echo '<div class="card event-card">';
                echo '  <div class="card-image" style="background-image: url(\'' . htmlspecialchars($poster) . '\');"></div>';
                echo '  <div class="card-content">';
                echo '      <span class="badge">' . htmlspecialchars($row['category_name']) . '</span>';
                echo '      <h3>' . htmlspecialchars($title) . '</h3>';
                echo '      <p class="location">' . htmlspecialchars($row['city_name']) . ', ' . htmlspecialchars($row['venue_name']) . '</p>';
                echo '      <p class="date">' . $date . '</p>';
                echo '      <div class="card-footer">';
                echo '          <span class="price">від ' . number_format($row['min_price'], 0) . ' грн</span>';
                echo '          <a href="event.php?id=' . $row['event_id'] . '" class="btn btn-buy">Купити квиток</a>';
                echo '      </div>';
                echo '  </div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    </div> </div> <?php include 'includes/footer.php'; ?>
</body>
</html>