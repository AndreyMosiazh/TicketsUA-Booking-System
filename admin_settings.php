<?php
require 'includes/db.php';
require_once 'includes/security.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php"); exit;
}

// Завантажуємо всі дані
$artists = $pdo->query("SELECT * FROM artists ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT * FROM event_categories ORDER BY name")->fetchAll();
$cities = $pdo->query("SELECT * FROM cities ORDER BY name")->fetchAll();
$venues = $pdo->query("SELECT v.*, c.name as city_name FROM venues v JOIN cities c ON v.city_id = c.city_id ORDER BY v.name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Довідники</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        @media (max-width: 800px) { .settings-grid { grid-template-columns: 1fr; } }
        
        .section-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        table { width: 100%; font-size: 14px; }
        td, th { padding: 8px; border-bottom: 1px solid #eee; }
        .btn-sm { padding: 4px 8px; font-size: 12px; margin-left: 2px; }
        .btn-edit { background: #ffc107; color: #000; }
        .btn-del { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h1>Налаштування довідників</h1>
            <a href="admin.php" class="btn" style="background:#6c757d;">&larr; Назад до сторінки адміністратора</a>
        </div>

        <div class="settings-grid">
            
            <div class="section-box">
                <div class="section-header">
                    <h3>Артисти</h3>
                    <button class="btn btn-buy btn-sm" onclick="openArtistModal()">+ Додати</button>
                </div>
                <table>
                    <?php foreach($artists as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['name']) ?></td>
                        <td style="text-align: right;">
                            <button class="btn btn-edit btn-sm" 
                                    onclick="openArtistModal(<?= $a['artist_id'] ?>, '<?= htmlspecialchars(addslashes($a['name'])) ?>', '<?= htmlspecialchars(addslashes($a['bio'])) ?>')">Редагувати</button>
                            <button class="btn btn-del btn-sm" onclick="deleteItem('delete_artist', <?= $a['artist_id'] ?>)">Видалити</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div class="section-box">
                <div class="section-header">
                    <h3>Категорії</h3>
                    <button class="btn btn-buy btn-sm" onclick="openCatModal()">+ Додати</button>
                </div>
                <table>
                    <?php foreach($categories as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td style="text-align: right;">
                            <button class="btn btn-edit btn-sm" onclick="openCatModal(<?= $c['category_id'] ?>, '<?= htmlspecialchars(addslashes($c['name'])) ?>')">Редагувати</button>
                            <button class="btn btn-del btn-sm" onclick="deleteItem('delete_category', <?= $c['category_id'] ?>)">Видалити</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div class="section-box">
                <div class="section-header">
                    <h3>Міста</h3>
                    <button class="btn btn-buy btn-sm" onclick="openCityModal()">+ Додати</button>
                </div>
                <table>
                    <?php foreach($cities as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td style="text-align: right;">
                            <button class="btn btn-edit btn-sm" onclick="openCityModal(<?= $c['city_id'] ?>, '<?= htmlspecialchars(addslashes($c['name'])) ?>')">Редагувати</button>
                            <button class="btn btn-del btn-sm" onclick="deleteItem('delete_city', <?= $c['city_id'] ?>)">Видалити</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div class="section-box">
                <div class="section-header">
                    <h3>Зали</h3>
                    <small>Додавати зали краще через конструктор подій</small>
                </div>
                <table>
                    <?php foreach($venues as $v): ?>
                    <tr>
                        <td>
                            <b><?= htmlspecialchars($v['name']) ?></b><br>
                            <small><?= htmlspecialchars($v['city_name']) ?>, <?= htmlspecialchars($v['address']) ?></small>
                        </td>
                        <td style="text-align: right;">
                            <button class="btn btn-edit btn-sm" 
                                    onclick="openVenueModal(<?= $v['venue_id'] ?>, '<?= htmlspecialchars(addslashes($v['name'])) ?>', '<?= htmlspecialchars(addslashes($v['address'])) ?>', <?= $v['city_id'] ?>)">Редагувати</button>
                            <button class="btn btn-del btn-sm" onclick="deleteItem('delete_venue', <?= $v['venue_id'] ?>)">Видалити</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <div id="modalArtist" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modalArtist')">&times;</span>
            <h2 id="artistTitle">Артист</h2>
            <form id="formArtist">
                <input type="hidden" name="action" id="artistAction" value="add_artist">
                <input type="hidden" name="id" id="artistId">
                <label>Ім'я:</label>
                <input type="text" name="name" id="artistName" required>
                <label>Біографія:</label>
                <textarea name="bio" id="artistBio"></textarea>
                <button type="submit" class="btn btn-buy" style="margin-top:10px;">Зберегти</button>
            </form>
        </div>
    </div>

    <div id="modalCat" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modalCat')">&times;</span>
            <h2 id="catTitle">Категорія</h2>
            <form id="formCat">
                <input type="hidden" name="action" id="catAction" value="add_category">
                <input type="hidden" name="id" id="catId">
                <label>Назва:</label>
                <input type="text" name="name" id="catName" required>
                <button type="submit" class="btn btn-buy" style="margin-top:10px;">Зберегти</button>
            </form>
        </div>
    </div>

    <div id="modalCity" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modalCity')">&times;</span>
            <h2 id="cityTitle">Місто</h2>
            <form id="formCity">
                <input type="hidden" name="action" id="cityAction" value="add_city">
                <input type="hidden" name="id" id="cityId">
                <label>Назва:</label>
                <input type="text" name="name" id="cityName" required>
                <button type="submit" class="btn btn-buy" style="margin-top:10px;">Зберегти</button>
            </form>
        </div>
    </div>

    <div id="modalVenue" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modalVenue')">&times;</span>
            <h2>Редагувати зал</h2>
            <form id="formVenue">
                <input type="hidden" name="action" value="edit_venue">
                <input type="hidden" name="id" id="venueId">
                
                <label>Назва:</label>
                <input type="text" name="name" id="venueName" required>
                
                <label>Місто:</label>
                <select name="city_id" id="venueCity">
                    <?php foreach($cities as $c): ?>
                        <option value="<?= $c['city_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Адреса:</label>
                <input type="text" name="address" id="venueAddress" required>

                <button type="submit" class="btn btn-buy" style="margin-top:10px;">Зберегти</button>
            </form>
        </div>
    </div>

    <script>
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        
        // --- ФУНКЦІЇ ВІДКРИТТЯ МОДАЛОК (ADD / EDIT) ---
        
        function openArtistModal(id = null, name = '', bio = '') {
            document.getElementById('modalArtist').style.display = 'block';
            document.getElementById('artistAction').value = id ? 'edit_artist' : 'add_artist';
            document.getElementById('artistId').value = id || '';
            document.getElementById('artistName').value = name;
            document.getElementById('artistBio').value = bio;
            document.getElementById('artistTitle').innerText = id ? 'Редагувати артиста' : 'Додати артиста';
        }

        function openCatModal(id = null, name = '') {
            document.getElementById('modalCat').style.display = 'block';
            document.getElementById('catAction').value = id ? 'edit_category' : 'add_category';
            document.getElementById('catId').value = id || '';
            document.getElementById('catName').value = name;
            document.getElementById('catTitle').innerText = id ? 'Редагувати категорію' : 'Додати категорію';
        }

        function openCityModal(id = null, name = '') {
            document.getElementById('modalCity').style.display = 'block';
            document.getElementById('cityAction').value = id ? 'edit_city' : 'add_city';
            document.getElementById('cityId').value = id || '';
            document.getElementById('cityName').value = name;
            document.getElementById('cityTitle').innerText = id ? 'Редагувати місто' : 'Додати місто';
        }

        function openVenueModal(id, name, address, cityId) {
            document.getElementById('modalVenue').style.display = 'block';
            document.getElementById('venueId').value = id;
            document.getElementById('venueName').value = name;
            document.getElementById('venueAddress').value = address;
            document.getElementById('venueCity').value = cityId;
        }

        // --- УНІВЕРСАЛЬНА ФУНКЦІЯ ВИДАЛЕННЯ ---
        function deleteItem(action, id) {
            if(!confirm('Ви впевнені? Якщо цей запис використовується, видалення буде заблоковано.')) return;

            const formData = new FormData();
            formData.append('action', action);
            formData.append('id', id);

            fetch('admin_api.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Помилка: ' + data.error);
                }
            });
        }

        // --- ОБРОБКА ФОРМ ---
        function handleForm(formId, modalId) {
            document.getElementById(formId).addEventListener('submit', function(e) {
                e.preventDefault();
                fetch('admin_api.php', { method: 'POST', body: new FormData(this) })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        location.reload(); 
                    } else {
                        alert('Помилка: ' + data.error);
                    }
                });
            });
        }

        handleForm('formArtist', 'modalArtist');
        handleForm('formCat', 'modalCat');
        handleForm('formCity', 'modalCity');
        handleForm('formVenue', 'modalVenue');

    </script>
</body>
</html>