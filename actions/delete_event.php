<?php
require '../includes/db.php';
require '../includes/security.php';

// Перевірка прав
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        $pdo->beginTransaction();

        // 1. Видаляємо квитки (якщо вони є), інакше БД не дасть видалити подію
        $pdo->prepare("DELETE FROM tickets WHERE event_id = ?")->execute([$id]);

        // 2. Видаляємо ціни
        $pdo->prepare("DELETE FROM event_prices WHERE event_id = ?")->execute([$id]);

        // 3. Видаляємо саму подію
        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        header("Location: ../admin.php?msg=deleted");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Помилка видалення: " . $e->getMessage());
    }
} else {
    header("Location: ../admin.php");
}

?>