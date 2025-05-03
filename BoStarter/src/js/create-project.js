function addReward() {
    const section = document.getElementById('rewards-section');
    const group = section.querySelector('.reward-group').cloneNode(true);
    group.querySelectorAll('input').forEach(input => input.value = '');
    section.appendChild(group);
}

// Mostra la sezione hardware solo se selezionato HARDWARE
function toggleHardwareSection() {
    var type = document.getElementById('type').value;
    document.getElementById('hardware-section').style.display = (type === 'HARDWARE') ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleHardwareSection);

// Aggiungi/rimuovi righe componenti
function addComponentRow() {
    var container = document.getElementById('components-list');
    var row = container.querySelector('.component-row').cloneNode(true);
    row.querySelector('select').selectedIndex = 0;
    row.querySelector('input').value = 1;
    container.appendChild(row);
}
function removeComponentRow(btn) {
    var container = document.getElementById('components-list');
    if (container.querySelectorAll('.component-row').length > 1) {
        btn.closest('.component-row').remove();
    }
}
