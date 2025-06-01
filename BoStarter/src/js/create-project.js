document.addEventListener('DOMContentLoaded', function () {
    // Modale rewards
    let rewards = [];

    document.getElementById('addRewardButton').addEventListener('click', function () {
        let form = document.getElementById('rewardForm');
        let nameInput = document.getElementById('rewardName');
        let descInput = document.getElementById('rewardDescription');
        let imageInput = document.getElementById('rewardImage');

        if (!nameInput.value || !descInput.value || imageInput.files.length === 0) {
            window.alert('Compila tutti i campi obbligatori.');
            return;
        }

        // Controllo duplicati
        let nomeLower = nameInput.value.trim().toLowerCase();
        if (rewards.some(r => r.name.toLowerCase() === nomeLower)) {
            alert('Una reward con questo nome esiste già.');
            return;
        }

        let reader = new FileReader();
        reader.onload = function (e) {
            let imgSrc = e.target.result;
            let idx = rewards.length;
            rewards.push({
                name: nameInput.value.trim(),
                description: descInput.value.trim(),
                file: imageInput.files[0]
            });

            let li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = ''
                + '<div><strong>' + nameInput.value.trim() + '</strong><p class="mb-1">' + descInput.value.trim() + '</p></div>'
                + '<img src="' + imgSrc + '" alt="' + nameInput.value.trim() + '" class="img-thumbnail" style="max-width: 100px;">'
                + '<button type="button" class="btn btn-danger btn-sm remove-reward" data-index="' + idx + '">Rimuovi</button>';
            document.getElementById('rewardList').appendChild(li);

            let rewardLi = document.getElementById('rewardList').lastElementChild;
            let rewardImg = rewardLi.querySelector('img');
            if (rewardImg) {
                rewardImg.style.cursor = 'pointer';
                rewardImg.addEventListener('click', function () {
                    let modalImg = document.getElementById('imgZoomModalImg');
                    modalImg.src = rewardImg.src;
                    $('#imgZoomModal').modal('show');
                });
            }

            // Chiudo il modal e resetto i campi
            $('#rewardModal').modal('hide');

        };
        reader.readAsDataURL(imageInput.files[0]);
    });

    // Rimuovi dalla lista di reward
    document.getElementById('rewardList').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-reward')) {
            let idx = parseInt(e.target.getAttribute('data-index'), 10);
            rewards.splice(idx, 1);
            e.target.parentElement.remove();
        }
    });


    // Modale componenti
    let components = [];

    document.getElementById('addComponentButton').addEventListener('click', function () {
        let form = document.getElementById('componentForm');
        let compSelect = document.getElementById('componentSelect');
        let qtyInput = document.getElementById('componentQuantity');

        if (!compSelect.value || !qtyInput.value) {
            window.alert('Compila tutti i campi obbligatori.');
            return;
        }

        // Controllo duplicati
        let compId = compSelect.value;
        if (components.some(c => c.id === compId)) {
            alert('La componente è già stata aggiunta.');
            return;
        }

        let compName = compSelect.options[compSelect.selectedIndex].text;
        let qty = parseInt(qtyInput.value, 10);
        let idx = components.length;
        components.push({ id: compId, name: compName, quantity: qty });

        let li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = ''
            + '<div><strong>' + compName + '</strong><span class="ml-2">Qtà: ' + qty + '</span></div>'
            + '<button type="button" class="btn btn-danger btn-sm remove-component" data-index="' + idx + '">Rimuovi</button>';
        document.getElementById('componentList').appendChild(li);

        // Chiudo modal e resetto i campi
        $('#componentModal').modal('hide');

    });

    // Rimuovi dalla lista componenti
    document.getElementById('componentList').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-component')) {
            let idx = parseInt(e.target.getAttribute('data-index'), 10);
            components.splice(idx, 1);
            e.target.parentElement.remove();
            // Ri‐indiceggio i data-index dei restanti
            Array.from(document.querySelectorAll('#componentList .remove-component')).forEach((btn, i) => {
                btn.setAttribute('data-index', i);
            });
        }
    });


    // Crea progetto
    document.querySelector('form[action="/create-project"]').addEventListener('submit', function (e) {
        // Controllo almeno 1 Reward
        if (rewards.length === 0) {
            alert('Devi aggiungere almeno una reward.');
            e.preventDefault();
            return;
        }

        // Clono i file delle reward dentro il form principale
        rewards.forEach(function (r, i) {
            let clonedFileInput = document.createElement('input');
            clonedFileInput.type = 'file';
            clonedFileInput.name = 'reward_image[]';
            clonedFileInput.style.display = 'none';
            let dt = new DataTransfer();
            dt.items.add(r.file);
            clonedFileInput.files = dt.files;
            e.target.appendChild(clonedFileInput);

            let hiddenDesc = document.createElement('input');
            hiddenDesc.type = 'hidden';
            hiddenDesc.name = 'reward_description[]';
            hiddenDesc.value = r.description;
            e.target.appendChild(hiddenDesc);
        });

        // Clono le componenti
        components.forEach(function (c, i) {
            let hiddenName = document.createElement('input');
            hiddenName.type = 'hidden';
            hiddenName.name = 'component_name[]';
            hiddenName.value = c.name;
            e.target.appendChild(hiddenName);

            let hiddenQty = document.createElement('input');
            hiddenQty.type = 'hidden';
            hiddenQty.name = 'component_qty[]';
            hiddenQty.value = c.quantity;
            e.target.appendChild(hiddenQty);
        });
    });


    // Visibilità sezione componenti/profili
    function updateComponentSection() {
        let val = document.getElementById('type').value;
        let section = document.getElementById('componentSection');
        let info = document.getElementById('componentInfo');
        if (val === 'SOFTWARE') {
            section.style.display = 'none';
            info.style.display = '';
        } else {
            section.style.display = '';
            info.style.display = 'none';
        }
    }
    updateComponentSection();
    document.getElementById('type').addEventListener('change', updateComponentSection);


    // Pulizia dei modali
    $('#rewardModal').on('hidden.bs.modal', function () {
        $('#rewardName').val('');
        $('#rewardDescription').val('');
        $('#rewardImage').val('');
    });
    $('#componentModal').on('hidden.bs.modal', function () {
        document.getElementById('componentForm').reset();
    });
    $('#newComponentModal').on('hidden.bs.modal', function () {
        document.getElementById('newComponentForm').reset();
    });

    // Galleria foto
    document.getElementById('images').addEventListener('change', function (e) {
        let previewContainer = document.getElementById('galleryPreview');
        previewContainer.innerHTML = ''; // Pulisci anteprime precedenti
        let files = e.target.files;
        Array.from(files).forEach(function (file, index) {
            if (!file.type.startsWith('image/')) return;
            let reader = new FileReader();
            reader.onload = function (ev) {
                let img = document.createElement('img');
                img.src = ev.target.result;
                img.className = 'img-thumbnail m-1';
                img.style.maxWidth = '150px';
                img.style.cursor = 'pointer';

                // Al click mostro il modal con immagine ingrandita
                img.addEventListener('click', function () {
                    let modalImg = document.getElementById('imgZoomModalImg');
                    modalImg.src = ev.target.result;
                    $('#imgZoomModal').modal('show');
                });
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
});
