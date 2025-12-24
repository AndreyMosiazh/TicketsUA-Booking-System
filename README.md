# TicketsUA — Online Booking System

Курсовий проєкт.
Система онлайн-бронювання квитків на події (концерти, кіно, театри) з можливістю вибору місць та генерацією QR-квитків.

## Функціонал

* **Користувач:**
    * Реєстрація та вхід.
    * Пошук подій (фільтри за категоріями та назвою).
    * Інтерактивна схема залу (вибір місць).
    * Особистий кабінет з історією замовлень.
    * **QR-квиток:** Генерація реального QR-коду з даними про вхід.
* **Адміністратор:**
    * Керування подіями, артистами, залами та категоріями.
    * Конструктор залів (налаштування рядів та типів місць).
    * Аналітика продажів (Dashboard).
    * Сканер квитків (симуляція валідації на вході).

## Технології

* **Backend:** PHP 8.2 (Native, PDO)
* **Database:** MySQL 8.0 (Relational, Transactions used for booking integrity)
* **Frontend:** HTML5, CSS3, JavaScript (AJAX for admin forms)
* **Environment:** Docker & Docker Compose

## Як запустити (Docker)

Проєкт повністю контейнеризований. Для запуску потрібно мати встановлений Docker.

1.  **Клонуйте репозиторій:**
    ```bash
    git clone [https://github.com/AndreyMosiazh/TicketsUA-Booking-System.git](https://github.com/AndreyMosiazh/TicketsUA-Booking-System.git)
    cd TicketsUA-Booking-System
    ```

2.  **Запустіть контейнери:**
    ```bash
    docker-compose up -d --build
    ```

3.  **Відкрийте у браузері:**
    * **Сайт:** [http://localhost:8080](http://localhost:8080)
    * **phpMyAdmin:** [http://localhost:8081](http://localhost:8081)


## Тестові дані (Credentials)

Для перевірки системи можна використати вже створені акаунти:

| Роль | Email | Пароль |
| :--- | :--- | :--- |
| **Admin** | `admin@example.com` | `123` |
| **User** | `client@test.com` | `123`  |
