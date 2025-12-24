

document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.seat-checkbox');
    const summaryBar = document.getElementById('summaryBar');
    const countSpan = document.getElementById('count');
    const totalSpan = document.getElementById('total');

    checkboxes.forEach(box => {
        box.addEventListener('change', updateSummary);
    });

    function updateSummary() {
        let count = 0;
        let total = 0;

        checkboxes.forEach(box => {
            if (box.checked) {
                count++;
                // Беремо ціну з data-атрибуту, який ми згенерували в PHP
                total += parseFloat(box.dataset.price);
            }
        });

        countSpan.innerText = count;
        totalSpan.innerText = total.toFixed(2);

        // Анімація: показуємо панель тільки якщо вибрано хоч одне місце
        if (count > 0) {
            summaryBar.classList.add('active');
        } else {
            summaryBar.classList.remove('active');
        }
    }
});