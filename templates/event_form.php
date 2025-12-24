<?php


// Визначаємо, чи це редагування
$is_edit = isset($event) && $event;
// Куди відправляти форму
$action_url = $is_edit ? 'actions/save_event.php?id=' . $event['event_id'] : 'actions/save_event.php';
?>

<form action="<?= $action_url ?>" method="POST" enctype="multipart/form-data" class="card">
    <?= csrfField() ?>
    
    <div class="form-group">
        <label>Назва події:</label>
        <input type="text" name="title" value="<?= $is_edit ? htmlspecialchars($event['title']) : '' ?>" required>
    </div>

    <div class="form-group">
        <label>Артист:</label>
        <div class="input-group">
            <select name="artist_id" id="artistSelect">
                <option value="">-- Не вказано / Видалити артиста --</option>
                
                <?php foreach ($options['artists'] as $a): ?>
                    <option value="<?= $a['artist_id'] ?>" <?= ($is_edit && $event['artist_id'] == $a['artist_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn-add" onclick="openModal('modalArtist')">+</button>
        </div>
    </div>

    <div class="form-group">
        <label>Категорія:</label>
        <div class="input-group">
            <select name="category_id" id="categorySelect" required>
                <?php foreach ($options['categories'] as $c): ?>
                    <option value="<?= $c['category_id'] ?>" <?= ($is_edit && $event['category_id'] == $c['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn-add" onclick="openModal('modalCategory')">+</button>
        </div>
    </div>

    <div class="form-group">
        <label>Локація (Зал):</label>
        <div class="input-group">
            <select name="venue_id" id="venueSelect" required>
                <?php foreach ($options['venues'] as $v): ?>
                    <option value="<?= $v['venue_id'] ?>" <?= ($is_edit && $event['venue_id'] == $v['venue_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['name']) ?> (<?= htmlspecialchars($v['city_name']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn-add" onclick="openModal('modalVenue')">+</button>
        </div>
    </div>

    <div class="form-group">
        <label>Дата початку:</label>
        <input type="datetime-local" name="start_time" 
               value="<?= $is_edit ? date('Y-m-d\TH:i', strtotime($event['start_time'])) : '' ?>" required>
    </div>

    <div class="form-group">
        <label>Опис:</label>
        <textarea name="description" rows="4"><?= $is_edit ? htmlspecialchars($event['description']) : '' ?></textarea>
    </div>

    <div class="form-group">
        <label>Постер:</label>
        <?php if ($is_edit && $event['poster_url']): ?>
            <div style="margin-bottom:10px; background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 5px; display: inline-block;">
                <img src="<?= $event['poster_url'] ?>" style="height: 100px; border-radius: 5px; vertical-align: middle;">
                
                <label style="display: inline-block; margin-left: 15px; cursor: pointer; color: #dc3545;">
                    <input type="checkbox" name="delete_poster" value="1"> Видалити постер
                </label>
            </div>
        <?php endif; ?>
        <input type="file" name="poster">
    </div>

    <hr>
    <h3>Ціни на квитки</h3>
    <p><small>Вкажіть ціну для типів місць, які є в обраному залі.</small></p>
    <?php foreach ($options['seat_types'] as $t): ?>
        <?php 
            $val = '';
            if ($is_edit && isset($current_prices[$t['type_id']])) {
                $val = $current_prices[$t['type_id']];
            }
        ?>
        <div style="margin-bottom: 5px;">
            <label><?= htmlspecialchars($t['name']) ?> (грн):</label>
            <input type="number" name="prices[<?= $t['type_id'] ?>]" value="<?= $val ?>" placeholder="0" min="0">
        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-buy" style="width:100%; margin-top:20px;">
        <?= $is_edit ? 'Зберегти зміни' : 'Створити подію' ?>
    </button>
</form>

<div id="modalArtist" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modalArtist')">&times;</span>
        <h2>Новий артист</h2>
        <form id="formArtist">
            <input type="hidden" name="action" value="add_artist">
            <label>Ім'я:</label>
            <input type="text" name="name" required>
            <label>Біографія:</label>
            <textarea name="bio"></textarea>
            <button type="submit" class="btn btn-buy" style="margin-top:10px;">Зберегти</button>
        </form>
    </div>
</div>

<div id="modalCategory" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modalCategory')">&times;</span>
        <h2>Нова категорія</h2>
        <form id="formCategory">
            <input type="hidden" name="action" value="add_category">
            <label>Назва:</label>
            <input type="text" name="name" required>
            <button type="submit" class="btn btn-buy" style="margin-top:10px;">Зберегти</button>
        </form>
    </div>
</div>

<div id="modalCity" class="modal" style="z-index: 1002;"> 
    <div class="modal-content">
        <span class="close" onclick="closeModal('modalCity')">&times;</span>
        <h2>Нове місто</h2>
        <form id="formCity">
            <input type="hidden" name="action" value="add_city">
            <label>Назва міста:</label>
            <input type="text" name="name" required>
            <button type="submit" class="btn btn-buy" style="margin-top:10px;">Зберегти</button>
        </form>
    </div>
</div>

<div id="modalVenue" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modalVenue')">&times;</span>
        <h2>Новий зал (Конструктор)</h2>
        
        <form id="formVenue">
            <input type="hidden" name="action" value="add_venue">
            
            <label>Назва залу:</label>
            <input type="text" name="name" placeholder="Напр: Зал №1" required>
            
            <label>Місто:</label>
            <div class="input-group">
                <select name="city_id" id="citySelect">
                    <?php                     
                    $all_cities = $pdo->query("SELECT * FROM cities")->fetchAll();
                    foreach ($all_cities as $city): ?>
                        <option value="<?= $city['city_id'] ?>"><?= htmlspecialchars($city['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn-add" onclick="openModal('modalCity')">+</button>
            </div>

            <label>Адреса:</label>
            <input type="text" name="address" required>

            <hr style="margin: 15px 0;">
            
            <h3>Конфігурація місць</h3>
            <div style="background: #f0f0f0; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                <div style="display: flex; gap: 5px; margin-bottom: 5px;">
                    <input type="number" id="z_start" placeholder="Ряд з" style="width: 60px">
                    <input type="number" id="z_end" placeholder="Ряд по" style="width: 60px">
                    <input type="number" id="z_count" placeholder="Місць" style="width: 60px">
                </div>
                <select id="z_type" style="margin-bottom: 5px;">
                    <?php foreach ($options['seat_types'] as $st): ?>
                        <option value="<?= $st['type_id'] ?>"><?= htmlspecialchars($st['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn" onclick="addZone()" style="width: 100%; background: #6c757d; color: white;">Додати зону</button>
            </div>

            <div id="zonesContainer" class="zone-list">
                <small>Зони залу (додайте хоча б одну):</small>
            </div>

            <input type="hidden" name="zones" id="zonesJSON">

            <button type="submit" class="btn btn-buy" style="margin-top:15px; width:100%;">Створити зал та місця</button>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'block'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    // --- ЛОГІКА КОНСТРУКТОРА ЗОН ---
    let zones = [];

    function addZone() {
        const start = document.getElementById('z_start').value;
        const end = document.getElementById('z_end').value;
        const count = document.getElementById('z_count').value;
        const typeSelect = document.getElementById('z_type');
        const typeId = typeSelect.value;
        const typeName = typeSelect.options[typeSelect.selectedIndex].text;

        if(!start || !end || !count) {
            alert("Заповніть всі поля зони!");
            return;
        }

        const zone = {
            start: start,
            end: end,
            count: count,
            type: typeId,
            typeName: typeName
        };
        zones.push(zone);
        renderZones();

        document.getElementById('z_start').value = '';
        document.getElementById('z_end').value = '';
    }

    function removeZone(index) {
        zones.splice(index, 1);
        renderZones();
    }

    function renderZones() {
        const container = document.getElementById('zonesContainer');
        container.innerHTML = '';
        
        if (zones.length === 0) {
            container.innerHTML = '<small>Список порожній...</small>';
            return;
        }

        zones.forEach((zone, index) => {
            const div = document.createElement('div');
            div.className = 'zone-item';
            div.innerHTML = `
                <span>Ряди <b>${zone.start}-${zone.end}</b>: ${zone.count} місць (${zone.typeName})</span>
                <span class="remove-zone" onclick="removeZone(${index})">×</span>
            `;
            container.appendChild(div);
        });
        
        document.getElementById('zonesJSON').value = JSON.stringify(zones);
    }

    // --- AJAX ВІДПРАВКА ---
    function handleForm(formId, selectId) {
        const form = document.getElementById(formId);
        if(!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (formId === 'formVenue' && zones.length === 0) {
                alert("Додайте хоча б одну зону місць!");
                return;
            }

            const formData = new FormData(this);

            fetch('admin_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    alert('ПОМИЛКА СЕРВЕРА:\n' + text);
                    console.error(text);
                    throw new Error('Server response is not JSON');
                }
            })
            .then(data => {
                if (data.success) {
                    alert('Успішно додано!');
                    
                    const select = document.getElementById(selectId);
                    if(select) {
                        const option = new Option(data.name, data.id);
                        select.add(option);
                        select.value = data.id;
                    }

                    closeModal(document.getElementById(formId).closest('.modal').id);
                    document.getElementById(formId).reset();
                    
                    if (formId === 'formVenue') {
                        zones = [];
                        renderZones();
                    }
                } else {
                    alert('Помилка: ' + data.error);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    // Ініціалізація обробників
    document.addEventListener('DOMContentLoaded', () => {
        handleForm('formArtist', 'artistSelect');
        handleForm('formCategory', 'categorySelect');
        handleForm('formVenue', 'venueSelect');
        handleForm('formCity', 'citySelect');
    });
</script>

<style>
    /* Стилі для конструктора, щоб вони були під рукою */
    .zone-list { margin-top: 10px; border: 1px solid #ddd; padding: 10px; background: #fafafa; border-radius: 5px; }
    .zone-item { display: flex; justify-content: space-between; background: white; padding: 5px; margin-bottom: 5px; border: 1px solid #eee; }
    .remove-zone { color: red; cursor: pointer; font-weight: bold; }
    
    /* Input-group для кнопок плюсик */
    .input-group { display: flex; gap: 10px; }
    .input-group select { flex: 1; }
    .btn-add { 
        background: #17a2b8; color: white; border: none; 
        width: 45px; font-size: 24px; line-height: 1;
        cursor: pointer; border-radius: 5px; 
        display: flex; align-items: center; justify-content: center;
    }
    .btn-add:hover { background: #138496; }
</style>