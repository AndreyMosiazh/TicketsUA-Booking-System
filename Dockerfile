# 1. Беремо за основу офіційний образ PHP з Apache
FROM php:8.2-apache

# 2. Встановлюємо розширення для роботи з базою даних (PDO)
RUN docker-php-ext-install pdo pdo_mysql

# 3. Вмикаємо модуль Apache для красивих посилань (mod_rewrite) - корисно на майбутнє
RUN a2enmod rewrite

# 4. Копіюємо файли вашого сайту всередину контейнера
COPY . /var/www/html/

# 5. Вказуємо права доступу (щоб сервер міг завантажувати картинки)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html