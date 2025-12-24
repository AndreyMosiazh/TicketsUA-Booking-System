<?php

/**
 * Завантаження картинки
 * Повертає шлях до файлу або викидає Exception з помилкою
 */
function uploadPoster($file) {
    if ($file['error'] !== 0) return null; // Файл не завантажували

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5 MB

    if ($file['size'] > $max_size) throw new Exception("Файл завеликий (макс 5MB)");
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    
    if (!in_array($mime, $allowed)) throw new Exception("Недопустимий формат ($mime)");

    $ext_map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $new_name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext_map[$mime];
    $path = 'uploads/' . $new_name;

    if (!is_dir('uploads/')) mkdir('uploads/');
    
    if (move_uploaded_file($file['tmp_name'], $path)) {
        return $path;
    }
    throw new Exception("Помилка запису файлу");
}

/**
 * Отримання списків для форм (Select options)
 */
function getFormOptions($pdo) {
    return [
        'venues' => $pdo->query("SELECT v.*, c.name as city_name FROM venues v JOIN cities c ON v.city_id = c.city_id")->fetchAll(),
        'artists' => $pdo->query("SELECT * FROM artists")->fetchAll(),
        'categories' => $pdo->query("SELECT * FROM event_categories")->fetchAll(),
        'seat_types' => $pdo->query("SELECT * FROM seat_types")->fetchAll()
    ];
}
?>