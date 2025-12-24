<div style="background: #333; padding: 15px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
    <div class="logo">
        <a href="index.php" style="color: white; text-decoration: none; font-weight: bold; font-size: 1.4em; display: flex; align-items: center;">
            TicketsUA
        </a>
    </div>
    <nav>
        <a href="index.php" class="nav-link">Афіша</a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="admin.php" class="nav-link" style="color: #ffc107;"> Адміністратор</a>
            <?php endif; ?>
            
            <a href="profile.php" class="nav-link">Мій кабінет</a>
            <span style="margin: 0 10px; color: #aaa;">|</span>
            <a href="logout.php" class="nav-link" style="color: #ff6b6b;">Вийти</a>
        
        <?php else: ?>
            <a href="login.php" class="nav-link">Увійти</a>
            <a href="register.php" class="nav-link">Реєстрація</a>
        <?php endif; ?>
    </nav>
</div>
<style>
    .nav-link { color: #ddd; text-decoration: none; margin-left: 20px; transition: color 0.2s; }
    .nav-link:hover { color: white; text-decoration: none; }
</style>