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
    <link rel="stylesheet" href="/style/create-project.css">
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
            <input type="number" class="form-control" id="budget" name="budget" min="1" step="0.01" placeholder="Inserisci l'obiettivo di budget da raggiungere" required>
        </div>

        <div class="form-group mb-4">
            <label for="deadline" class="font-weight-bold">Data limite</label>
            <input type="date" class="form-control" id="deadline" name="deadline" placeholder="Seleziona la data limite" required>
        </div>

        <div class="form-group mb-4">
            <label for="images" class="font-weight-bold">Galleria (puoi caricare più immagini)</label>
            <input type="file" class="form-control-file" id="images" name="images[]" accept="image/*" multiple required>
        </div>
        <div id="galleryPreview" class="mt-3 d-flex flex-wrap"></div>
    
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
                <div id="rewardForm">
                  <div class="form-group">
                    <label for="rewardName" class="font-weight-bold">Nome reward</label>
                    <input type="text" class="form-control" id="rewardName" name="rewardNameModal" placeholder="Inserisci nome reward" required>
                  </div>
                  <div class="form-group">
                    <label for="rewardDescription" class="font-weight-bold">Descrizione</label>
                    <textarea class="form-control" id="rewardDescription" name="rewardDescriptionModal" rows="3" placeholder="Inserisci descrizione reward" required></textarea>
                  </div>
                  <div class="form-group">
                    <label for="rewardImage" class="font-weight-bold">Immagine</label>
                    <input type="file" class="form-control-file" id="rewardImage" name="rewardImageModal" accept="image/*" required>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-primary" id="addRewardButton">Aggiungi</button>
              </div>
            </div>
          </div>
        </div>
    </form>
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
                  <?php foreach ($allComponents as $comp):
                      $compKey = isset($comp['nome']) ? $comp['nome'] : '';
                      $compPrice = isset($comp['prezzo']) ? number_format((float)$comp['prezzo'], 2, ',', '.') : '0,00';
                  ?>
                    <option value="<?php echo htmlspecialchars((string)$compKey); ?>">
                      <?php echo htmlspecialchars((string)$compKey); ?> (&#8364;<?php echo htmlspecialchars($compPrice); ?>)
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
          <form id="newComponentForm" action="/create-project" method="post">
            <input type="hidden" name="add_component" value="1">
            <div class="modal-body">
                <div class="form-group">
                  <label for="newComponentName" class="font-weight-bold">Nome componente</label>
                  <input type="text" class="form-control" id="newComponentName" name="new_component_name" placeholder="Inserisci nome componente" required>
                </div>
                <div class="form-group">
                  <label for="newComponentDescription" class="font-weight-bold">Descrizione</label>
                  <textarea class="form-control" id="newComponentDescription" name="new_component_desc" rows="3" placeholder="Inserisci descrizione componente" required></textarea>
                </div>
                <div class="form-group">
                  <label for="newComponentPrice" class="font-weight-bold">Prezzo (€)</label>
                  <input type="number" class="form-control" id="newComponentPrice" name="new_component_price" min="0.01" step="0.01" placeholder="Inserisci prezzo componente" required>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
              <button type="submit" class="btn btn-primary">Crea componente</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modale per zoom immagini -->
    <div class="modal fade" id="imgZoomModal" tabindex="-1" role="dialog" aria-labelledby="imgZoomModalLabel" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content bg-transparent border-0">
          <div class="modal-body text-center p-0">
            <img src="" id="imgZoomModalImg" class="img-fluid rounded border border-light shadow-lg p-2 bg-white" alt="Zoom immagine">
          </div>
        </div>
      </div>
    </div>
</div>
</body>
</html>
