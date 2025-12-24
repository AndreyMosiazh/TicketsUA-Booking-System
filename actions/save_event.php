<?php
require '../includes/db.php';
require '../includes/security.php';
require '../includes/functions.php';

// Перевірка прав доступу
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    try {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        // Отримуємо дані з форми
        $title = trim($_POST['title']);
        $venue_id = $_POST['venue_id'];
        $artist_id = !empty($_POST['artist_id']) ? $_POST['artist_id'] : NULL;
        $category_id = $_POST['category_id'];
        $start_time = $_POST['start_time'];
        $description = $_POST['description'];
        $prices = $_POST['prices'] ?? [];

        // --- ЛОГІКА ОБРОБКИ ПОСТЕРА ---
        $poster_sql_part = "";
        // Базові параметри для INSERT
        $params = [$title, $description, $venue_id, $artist_id, $category_id, $start_time];

        // 1. Якщо завантажили новий файл
        if (!empty($_FILES['poster']['name'])) {
            $path = uploadPoster($_FILES['poster']);
            if ($id) {
                // Якщо редагування: додаємо SQL частину і параметр
                $poster_sql_part = ", poster_url = ?";
                $params[] = $path; 
            } else {
                // Якщо створення: просто додаємо шлях
                $params[] = $path;
            }
        } 
        // 2. Якщо поставили галочку "Видалити постер" (Тільки при редагуванні)
        elseif ($id && isset($_POST['delete_poster']) && $_POST['delete_poster'] == '1') {
            $poster_sql_part = ", poster_url = NULL";
            // Параметр не додаємо, бо NULL прописаний прямо в SQL
        }
        // 3. Якщо створюємо нову подію без постера
        elseif (!$id) {
            $params[] = ''; // Порожній рядок замість URL
        }

        $pdo->beginTransaction();

        if ($id) {
            // --- РЕДАГУВАННЯ ІСНУЮЧОЇ ПОДІЇ ---
            // Додаємо ID події в кінець параметрів (для WHERE event_id = ?)
            $params[] = $id; 

            $sql = "UPDATE events SET title=?, description=?, venue_id=?, artist_id=?, category_id=?, start_time=? $poster_sql_part WHERE event_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Видаляємо старі ціни, щоб перезаписати нові
            $pdo->prepare("DELETE FROM event_prices WHERE event_id = ?")->execute([$id]);
            $event_id = $id;
        } else {
            // --- СТВОРЕННЯ НОВОЇ ПОДІЇ ---
            $sql = "INSERT INTO events (title, description, venue_id, artist_id, category_id, start_time, poster_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $event_id = $pdo->lastInsertId();
        }

        // --- ЗБЕРЕЖЕННЯ ЦІН ---
        $price_stmt = $pdo->prepare("INSERT INTO event_prices (event_id, seat_type_id, price) VALUES (?, ?, ?)");
        foreach ($prices as $tid => $price) {
            // Зберігаємо ціну тільки якщо вона більше 0
            if ($price > 0) {
                $price_stmt->execute([$event_id, $tid, $price]);
            }
        }

        $pdo->commit();
        header("Location: ../admin.php?msg=saved");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Помилка збереження: " . $e->getMessage());
    }
}
?>