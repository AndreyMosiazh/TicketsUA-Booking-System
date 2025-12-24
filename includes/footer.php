<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            
            <div class="footer-section">
                <h3>TicketsUA</h3>
                <p>Твій надійний сервіс для купівлі квитків на найкращі події країни. Концерти, кіно, театри — все в одному місці.</p>
            </div>

            <div class="footer-section">
                <h3>Меню</h3>
                <ul>
                    <li><a href="index.php">Афіша подій</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="profile.php">Мій кабінет</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Вхід</a></li>
                        <li><a href="register.php">Реєстрація</a></li>
                    <?php endif; ?>
                    <li><a href="#">Правила повернення</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Контакти</h3>
                <p>Київ, вул. Хрещатик, 1</p>
                <p>+38 (044) 123-45-67</p>
                <p>support@tickets.ua</p>
                <div class="socials">
                    <a href="#" class="social-icon">Instagram</a>
                    <a href="#" class="social-icon">Facebook</a>
                    <a href="#" class="social-icon">Telegram</a>
                </div>
            </div>

        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> TicketsUA. Всі права захищено.</p>
    </div>
</footer>