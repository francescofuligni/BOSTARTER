<?php
require_once __DIR__ . '/../controllers/CreateProjectController.php';
require_once __DIR__ . '/../models/Component.php';
require_once __DIR__ . '/components/navbar.php';

$db = new Database();
$conn = $db->getConnection();
$componentModel = new Component($conn);
$allComponents = $componentModel->getAllComponents()['data'];
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Crea nuovo progetto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="/js/create-project.js"></script>
</head>
<body>
<div class="container mt-5">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['info'])): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($_SESSION['info']); unset($_SESSION['info']); ?>
        </div>
    <?php endif; ?>
    <h1>Crea un nuovo progetto</h1>
    <form action="/create-project" method="post" enctype="multipart/form-data">

        <div class="form-group mb-4">
            <label for="name" class="font-weight-bold">Titolo</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Inserisci il nome del progetto" required>
        </div>

        <div class="form-group mb-4">
            <label for="description" class="font-weight-bold">Descrizione</label>
            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Inserisci la descrizione del progetto" required></textarea>
        </div>

        <div class="form-group mb-4">
            <label for="budget" class="font-weight-bold">Budget (€)</label>
            <input type="number" class="form-control" id="budget" name="budget" min="1" step="0.01" placeholder="Inserisci il budget previsto" required>
        </div>

        <div class="form-group mb-4">
            <label for="deadline" class="font-weight-bold">Data limite</label>
            <input type="date" class="form-control" id="deadline" name="deadline" placeholder="Seleziona la data limite" required>
        </div>

        <div class="form-group mb-4">
            <label for="images" class="font-weight-bold">Galleria (puoi caricare più immagini)</label>
            <input type="file" class="form-control-file" id="images" name="images[]" accept="image/*" multiple required>
        </div>
    
        <div class="form-group mb-4">
            <label for="type" class="font-weight-bold">Tipo</label>
            <select class="form-control" id="type" name="type" required>
                <option value="HARDWARE" selected>Progetto Hardware</option>
                <option value="SOFTWARE">Progetto Software</option>
            </select>
        </div>

        <div class="row mb-4">
          <div class="col-md-6">
            <div class="form-group">
              <label for="rewardButton" class="font-weight-bold d-block">Rewards</label>
              <button id="rewardButton" type="button" class="btn btn-primary mb-2" data-toggle="modal" data-target="#rewardModal">
                Aggiungi reward
              </button>
              <ul id="rewardList" class="list-group"></ul>
            </div>
          </div>
          <div class="col-md-6" style="border-left: 1px solid #dee2e6; padding-left: 1.5rem;">
            <div id="componentSection">
              <div class="form-group">
                <label for="componentButton" class="font-weight-bold d-block">Componenti</label>
                <button id="componentButton" type="button" class="btn btn-primary mb-2" data-toggle="modal" data-target="#componentModal">
                  Aggiungi componente
                </button>
                <ul id="componentList" class="list-group"></ul>
              </div>
            </div>
            <div id="componentInfo" class="form-group" style="display:none;">
              <label class="font-weight-bold d-block">Profili</label>
              <small class="text-info">Sarà possibile aggiungere profili al progetto successivamente, dalla pagina di dettaglio del progetto</small>
            </div>
          </div>
        </div>
    
        <button type="submit" class="btn btn-success">Crea progetto</button>
        <a href="/dashboard" class="btn btn-secondary">Annulla</a>
    </form>
</div>
    <div class="modal fade" id="rewardModal" tabindex="-1" role="dialog" aria-labelledby="rewardModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="rewardModalLabel">Aggiungi Reward</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="rewardForm">
              <div class="form-group">
                <label for="rewardName" class="font-weight-bold">Nome reward</label>
                <input type="text" class="form-control" id="rewardName" name="rewardName" placeholder="Inserisci nome reward" required>
              </div>
              <div class="form-group">
                <label for="rewardDescription" class="font-weight-bold">Descrizione</label>
                <textarea class="form-control" id="rewardDescription" name="rewardDescription" rows="3" placeholder="Inserisci descrizione reward" required></textarea>
              </div>
              <div class="form-group">
                <label for="rewardImage" class="font-weight-bold">Immagine</label>
                <input type="file" class="form-control-file" id="rewardImage" name="rewardImage" accept="image/*" required>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
            <button type="button" class="btn btn-primary" id="addRewardButton">Aggiungi</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="componentModal" tabindex="-1" role="dialog" aria-labelledby="componentModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="componentModalLabel">Aggiungi Componente</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="componentForm">
              <div class="form-group">
                <label for="componentSelect" class="font-weight-bold">Seleziona componente</label>
                <select class="form-control" id="componentSelect" name="componentSelect" required>
                  <option value="" disabled selected>Seleziona componente</option>
                  <?php foreach ($allComponents as $comp): ?>
                    <option value="<?php echo htmlspecialchars($comp['id']); ?>">
                      <?php echo htmlspecialchars($comp['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="componentQuantity" class="font-weight-bold">Quantità</label>
                <input type="number" class="form-control" id="componentQuantity" name="componentQuantity" min="1" value="1" placeholder="Inserisci quantità" required>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#newComponentModal">
              Crea componente
            </button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
            <button type="button" class="btn btn-primary" id="addComponentButton">Aggiungi</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="newComponentModal" tabindex="-1" role="dialog" aria-labelledby="newComponentModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="newComponentModalLabel">Crea Componente</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="newComponentForm">
              <div class="form-group">
                <label for="newComponentName" class="font-weight-bold">Nome componente</label>
                <input type="text" class="form-control" id="newComponentName" name="newComponentName" placeholder="Inserisci nome componente" required>
              </div>
              <div class="form-group">
                <label for="newComponentDescription" class="font-weight-bold">Descrizione</label>
                <textarea class="form-control" id="newComponentDescription" name="newComponentDescription" rows="3" placeholder="Inserisci descrizione componente" required></textarea>
              </div>
              <div class="form-group">
                <label for="newComponentPrice" class="font-weight-bold">Prezzo (€)</label>
                <input type="number" class="form-control" id="newComponentPrice" name="newComponentPrice" min="0.01" step="0.01" placeholder="Inserisci prezzo componente" required>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
            <button type="button" class="btn btn-primary" id="createComponentButton">Crea componente</button>
          </div>
        </div>
      </div>
    </div>
</div>
    <script>
      $(document).ready(function() {
        var rewards = [];
        $('button[type="submit"]').prop('disabled', true);

        $('#addRewardButton').click(function() {
          if ($('#rewardForm')[0].checkValidity()) {
            var name = $('#rewardName').val();
            // Prevent duplicate names
            if (rewards.some(function(r) { return r.name.toLowerCase() === name.toLowerCase(); })) {
              alert('Una reward con questo nome esiste già.');
              return;
            }
            var desc = $('#rewardDescription').val();
            var fileInput = $('#rewardImage')[0];
            var file = fileInput.files[0];
            var reader = new FileReader();
            reader.onload = function(e) {
              var imgSrc = e.target.result;
              var idx = rewards.length;
              rewards.push({ name: name, description: desc, image: imgSrc });
              $('#rewardList').append(
                '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                  '<div><strong>' + name + '</strong><p class="mb-1">' + desc + '</p></div>' +
                  '<img src="' + imgSrc + '" alt="' + name + '" class="img-thumbnail" style="max-width: 100px;">' +
                  '<button type="button" class="btn btn-danger btn-sm remove-reward" data-index="' + idx + '">Rimuovi</button>' +
                '</li>'
              );
              $('#rewardModal').modal('hide');
              $('#rewardForm')[0].reset();
              $('button[type="submit"]').prop('disabled', rewards.length === 0);
            };
            reader.readAsDataURL(file);
          } else {
            $('#rewardForm')[0].reportValidity();
          }
        });

        $('#rewardList').on('click', '.remove-reward', function() {
          var idx = $(this).data('index');
          rewards.splice(idx, 1);
          $('#rewardList').children().eq(idx).remove();
          $('button[type="submit"]').prop('disabled', rewards.length === 0);
        });

        // Componenti logic
        var components = [];
        $('#addComponentButton').click(function() {
          if ($('#componentForm')[0].checkValidity()) {
            var compId = $('#componentSelect').val();
            var compName = $('#componentSelect option:selected').text();
            var qty = parseInt($('#componentQuantity').val(), 10);
            // Check for duplicates
            if (components.some(function(c) { return c.id === compId; })) {
              alert('La componente è già stata aggiunta.');
              return;
            }
            var idx = components.length;
            components.push({ id: compId, name: compName, quantity: qty });
            $('#componentList').append(
              '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                '<div><strong>' + compName + '</strong><span class="ml-2">Qtà: ' + qty + '</span></div>' +
                '<button type="button" class="btn btn-danger btn-sm remove-component" data-index="' + idx + '">Rimuovi</button>' +
              '</li>'
            );
            $('#componentModal').modal('hide');
            $('#componentForm')[0].reset();
          } else {
            $('#componentForm')[0].reportValidity();
          }
        });
        $('#componentList').on('click', '.remove-component', function() {
          var idx = $(this).data('index');
          components.splice(idx, 1);
          $('#componentList').children().eq(idx).remove();
          // Re-index data-index attributes
          $('#componentList').children().each(function(i) {
            $(this).find('.remove-component').attr('data-index', i);
          });
        });

        $('form').submit(function(e) {
          if (rewards.length === 0) {
            alert('Devi aggiungere almeno una reward.');
            e.preventDefault();
          } else {
            // Append rewards data as hidden inputs
            rewards.forEach(function(r, i) {
              $('<input>').attr({ type: 'hidden', name: 'rewards[' + i + '][name]', value: r.name }).appendTo('form');
              $('<input>').attr({ type: 'hidden', name: 'rewards[' + i + '][description]', value: r.description }).appendTo('form');
              $('<input>').attr({ type: 'hidden', name: 'rewards[' + i + '][imageData]', value: r.image }).appendTo('form');
            });
            // Append components data as hidden inputs
            components.forEach(function(c, i) {
              $('<input>').attr({ type: 'hidden', name: 'components[' + i + '][id]', value: c.id }).appendTo('form');
              $('<input>').attr({ type: 'hidden', name: 'components[' + i + '][quantity]', value: c.quantity }).appendTo('form');
            });
          }
        });

        function updateComponentSection() {
          var val = $('#type').val();
          if (val === '') {
            $('#componentSection').hide();
            $('#componentInfo').hide();
            $('#componentPlaceholder').show();
          } else if (val === 'SOFTWARE') {
            $('#componentSection').hide();
            $('#componentPlaceholder').hide();
            $('#componentInfo').show();
          } else {
            $('#componentPlaceholder').hide();
            $('#componentInfo').hide();
            $('#componentSection').show();
          }
        }
        // Run on page load and when type changes
        updateComponentSection();
        $('#type').change(updateComponentSection);

        // Clear form fields on modal close
        $('#rewardModal').on('hidden.bs.modal', function() {
          $('#rewardForm')[0].reset();
        });
        $('#componentModal').on('hidden.bs.modal', function() {
          $('#componentForm')[0].reset();
        });
        $('#newComponentModal').on('hidden.bs.modal', function() {
          $('#newComponentForm')[0].reset();
        });

        // Validate new component form on create
        $('#createComponentButton').click(function() {
          if ($('#newComponentForm')[0].checkValidity()) {
            $('#newComponentModal').modal('hide');
          } else {
            $('#newComponentForm')[0].reportValidity();
          }
        });
      });
    </script>
</body>
</html>
