<?php
// admin_api.php
require 'includes/db.php';
require_once 'includes/security.php';

header('Content-Type: application/json');

// Перевірка прав доступу (тільки адмін)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    if ($action === 'add_artist') {
        $name = trim($_POST['name']);
        $bio = trim($_POST['bio']);
        
        $stmt = $pdo->prepare("INSERT INTO artists (name, bio) VALUES (?, ?)");
        $stmt->execute([$name, $bio]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'name' => $name]);
    }
    elseif ($action === 'edit_artist') {
        $stmt = $pdo->prepare("UPDATE artists SET name = ?, bio = ? WHERE artist_id = ?");
        $stmt->execute([trim($_POST['name']), trim($_POST['bio']), $_POST['id']]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete_artist') {
        // Перевіряємо, чи є події з цим артистом
        $check = $pdo->prepare("SELECT COUNT(*) FROM events WHERE artist_id = ?");
        $check->execute([$_POST['id']]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("Неможливо видалити: цей артист прив'язаний до подій!");
        }
        $pdo->prepare("DELETE FROM artists WHERE artist_id = ?")->execute([$_POST['id']]);
        echo json_encode(['success' => true]);
    }

    elseif ($action === 'add_category') {
        $name = trim($_POST['name']);
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));

        $stmt = $pdo->prepare("INSERT INTO event_categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'name' => $name]);
    }
    elseif ($action === 'edit_category') {
        $stmt = $pdo->prepare("UPDATE event_categories SET name = ? WHERE category_id = ?");
        $stmt->execute([trim($_POST['name']), $_POST['id']]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete_category') {
        $check = $pdo->prepare("SELECT COUNT(*) FROM events WHERE category_id = ?");
        $check->execute([$_POST['id']]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("Категорія використовується в подіях. Спочатку видаліть події.");
        }
        $pdo->prepare("DELETE FROM event_categories WHERE category_id = ?")->execute([$_POST['id']]);
        echo json_encode(['success' => true]);
    }

    elseif ($action === 'add_city') {
        $name = trim($_POST['name']);
        
        $stmt = $pdo->prepare("INSERT INTO cities (name) VALUES (?)");
        $stmt->execute([$name]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'name' => $name]);
    }
    elseif ($action === 'edit_city') {
        $stmt = $pdo->prepare("UPDATE cities SET name = ? WHERE city_id = ?");
        $stmt->execute([trim($_POST['name']), $_POST['id']]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete_city') {
        $check = $pdo->prepare("SELECT COUNT(*) FROM venues WHERE city_id = ?");
        $check->execute([$_POST['id']]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("У цьому місті є зали. Спочатку видаліть їх.");
        }
        $pdo->prepare("DELETE FROM cities WHERE city_id = ?")->execute([$_POST['id']]);
        echo json_encode(['success' => true]);
    }
    
    elseif ($action === 'add_venue') {
        $name = trim($_POST['name']);
        $city_id = $_POST['city_id'];
        $address = trim($_POST['address']);
        
        $zones = json_decode($_POST['zones'], true);

        if (!$zones || count($zones) === 0) {
            throw new Exception("Не додано жодної зони місць!");
        }

        $used_rows = []; // Масив для збереження вже зайнятих рядів

        foreach ($zones as $zone) {
            $start = (int)$zone['start'];
            $end = (int)$zone['end'];

            // Логічна перевірка: кінець не може бути менше початку
            if ($end < $start) {
                throw new Exception("Помилка в зоні: Ряд 'по' ($end) менший за ряд 'з' ($start).");
            }

            // Перевіряємо кожен ряд у діапазоні
            for ($r = $start; $r <= $end; $r++) {
                if (in_array($r, $used_rows)) {
                    throw new Exception("Помилка: Ряд №$r вже використовується в іншій зоні! Один ряд не може мати два типи місць.");
                }
                $used_rows[] = $r; // Записуємо ряд як зайнятий
            }
        }

        $pdo->beginTransaction();

        // А. Створюємо зал
        $stmt = $pdo->prepare("INSERT INTO venues (name, address, city_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $address, $city_id]);
        $venue_id = $pdo->lastInsertId();

        // Б. Генеруємо місця 
        $seat_sql = "INSERT INTO seats (venue_id, type_id, seat_row, seat_number) VALUES (?, ?, ?, ?)";
        $seat_stmt = $pdo->prepare($seat_sql);

        $seats_created = 0;

        foreach ($zones as $zone) {
            $row_start = (int)$zone['start'];
            $row_end   = (int)$zone['end'];
            $seats_count = (int)$zone['count'];
            $type_id   = (int)$zone['type'];

            for ($r = $row_start; $r <= $row_end; $r++) {
                // Цикл $s = 1 ... $seats_count генерує номери сидінь (1, 2, 3...)
                for ($s = 1; $s <= $seats_count; $s++) {
                    $seat_stmt->execute([$venue_id, $type_id, $r, $s]);
                    $seats_created++;
                }
            }
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'id' => $venue_id, 
            'name' => "$name ($address)",
            'message' => "Створено зал та $seats_created місць!"
        ]);
    }
    elseif ($action === 'edit_venue') {
        // Редагуємо тільки назву, адресу та місто 
        $stmt = $pdo->prepare("UPDATE venues SET name = ?, address = ?, city_id = ? WHERE venue_id = ?");
        $stmt->execute([trim($_POST['name']), trim($_POST['address']), $_POST['city_id'], $_POST['id']]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'delete_venue') {
        // Перевіряємо, чи є події в цьому залі
        $check = $pdo->prepare("SELECT COUNT(*) FROM events WHERE venue_id = ?");
        $check->execute([$_POST['id']]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("У цьому залі заплановані події. Видалення неможливе.");
        }
        // Завдяки ON DELETE CASCADE в базі даних, місця (seats) видаляться автоматично
        $pdo->prepare("DELETE FROM venues WHERE venue_id = ?")->execute([$_POST['id']]);
        echo json_encode(['success' => true]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>