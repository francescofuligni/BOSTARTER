function addReward() {
    const section = document.getElementById('rewards-section');
    const group = section.querySelector('.reward-group').cloneNode(true);
    group.querySelectorAll('input').forEach(input => input.value = '');
    section.appendChild(group);
}

// Mostra la sezione hardware solo se selezionato HARDWARE, altrimenti mostra info software
function toggleHardwareSection() {
    var type = document.getElementById('type').value;
    var hw = document.getElementById('hardware-section');
    var sw = document.getElementById('software-info');
    if (type === 'HARDWARE') {
        hw.style.display = 'block';
        sw.style.display = 'none';
    } else {
        hw.style.display = 'none';
        sw.style.display = 'block';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    toggleHardwareSection();
    document.getElementById('confirmComponentsBtn').addEventListener('click', confirmComponents);
});

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

function addModalComponentRow() {
    var container = document.getElementById('modal-components-list');
    var row = container.querySelector('.component-row').cloneNode(true);
    row.querySelector('select').selectedIndex = 0;
    row.querySelector('input').value = 1;
    container.appendChild(row);
}

function removeModalComponentRow(btn) {
    var container = document.getElementById('modal-components-list');
    if (container.querySelectorAll('.component-row').length > 1) {
        btn.closest('.component-row').remove();
    }
}

function confirmComponents() {
    var modalList = document.getElementById('modal-components-list');
    var selected = document.getElementById('selected-components');
    modalList.querySelectorAll('.component-row').forEach(function(row) {
        var select = row.querySelector('select');
        var nome = select.value;
        var qty  = row.querySelector('input').value;
        var price = select.selectedOptions[0].dataset.price;
        if (nome && !selected.querySelector('[data-name="'+nome+'"]')) {
            var div = document.createElement('div');
            div.className = 'row align-items-center mb-2';
            div.dataset.name = nome;
            div.innerHTML =
                '<div class="col-md-4">'+nome+'</div>' +
                '<div class="col-md-3">'+qty+'</div>' +
                '<div class="col-md-3">€'+parseFloat(price).toFixed(2).replace(".",",")+'</div>' +
                '<div class="col-md-2 text-right">' +
                  '<button type="button" class="btn btn-danger btn-sm" onclick="this.closest(\'.row\').remove()">Rimuovi</button>' +
                '</div>';
            selected.appendChild(div);
        }
    });
    // Reset modal rows
    while (modalList.children.length > 1) {
        modalList.removeChild(modalList.lastChild);
    }
    var first = modalList.querySelector('.component-row');
    first.querySelector('select').selectedIndex = 0;
    first.querySelector('input').value = 1;
    $('#addComponentsModal').modal('hide');
}
