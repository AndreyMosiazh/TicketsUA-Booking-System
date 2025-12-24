<?php
session_start();
/**
 * Файл підключення до бази даних (PDO).
 * Підтримує роботу як на локальному сервері (XAMPP/OpenServer),
 * так і в контейнері Docker через змінні оточення.
 */

// 1. Визначення параметрів підключення
// Логіка: "Якщо є змінна від Docker — беремо її, якщо ні — беремо локальне значення"

// Хост: у Docker це буде ім'я сервісу (напр. 'mysql'), локально — 'localhost'
$host = getenv('DB_HOST') ?: 'localhost';

// Назва бази даних
$db   = getenv('DB_NAME') ?: 'OnlineBooking';

// Користувач: у Docker часто створюють окремого юзера, локально зазвичай 'root'
$user = getenv('DB_USER') ?: 'root';

// Пароль: 
$pass = getenv('DB_PASSWORD') ?: 'root'; 

// Кодування (важливо для кирилиці та смайлів)
$charset = 'utf8mb4';

// 2. Налаштування DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// 3. Опції PDO
$options = [
    // Вмикаємо режим викидання помилок (Exception), щоб бачити проблеми в коді
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Результат повертається як асоціативний масив (['name' => 'Ivan']), а не числовий
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Вимикаємо емуляцію підготовлених запитів (для безпеки та чесних типів даних)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Спроба підключення
    $pdo = new PDO($dsn, $user, $pass, $options);
        
} catch (\PDOException $e) {
    // Якщо сталася помилка — зупиняємо скрипт і виводимо повідомлення
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>