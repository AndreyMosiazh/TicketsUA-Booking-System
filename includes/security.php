<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Генерация токена
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Поле для формы
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// Проверка токена
function verifyCsrf() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Помилка безпеки: CSRF токен недійсний. Оновіть сторінку.');
    }
}
?>