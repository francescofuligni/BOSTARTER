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
    <h2 class="mb-4">Crea un nuovo progetto</h2>
    <form action="/create-project" method="post" enctype="multipart/form-data">
        <div class="form-group mb-4">
            <label for="name"><strong>Titolo</strong></label>
            <input type="text" class="form-control" id="name" name="name"
                   placeholder="Inserisci il titolo del tuo progetto" required>
        </div>
        <div class="form-group mb-4">
            <label for="description"><strong>Descrizione</strong></label>
            <textarea class="form-control" id="description" name="description"
                      rows="4" placeholder="Descrivi il tuo progetto" required></textarea>
        </div>
        <div class="form-group mb-4">
            <label for="budget"><strong>Target budget (€)</strong></label>
            <input type="number" class="form-control" id="budget" name="budget"
                   placeholder="Es. 1000,00" min="1" step="0.01" required>
        </div>
        <div class="form-group mb-4">
            <label for="deadline"><strong>Data limite</strong></label>
            <input type="date" class="form-control" id="deadline" name="deadline"
                   placeholder="YYYY-MM-DD" required>
        </div>
        <div class="form-group mb-4">
            <label for="type"><strong>Tipo</strong></label>
            <select class="form-control" id="type" name="type" required onchange="toggleHardwareSection()">
                <option value="HARDWARE">Hardware</option>
                <option value="SOFTWARE">Software</option>
            </select>
        </div>
        <div class="form-group mb-4">
            <label for="immagini"><strong>Galleria foto</strong></label>
            <input type="file" class="form-control-file" id="immagini" name="immagini[]"
                   accept="image/*" multiple required>
            <small class="form-text text-muted">Puoi caricare più immagini</small>
        </div>
        <div class="row">
          <div class="col-md-6 pr-4 border-right">
            <div class="form-group mb-4">
              <label><strong>Componenti</strong></label>
              <div id="selected-components" class="mt-2"></div>
              <button type="button" class="btn btn-primary mt-2"
                      data-toggle="modal" data-target="#addComponentsModal">
                Aggiungi componenti hardware
              </button>
            </div>
          </div>
          <div class="col-md-6 pl-4">
            <div class="form-group mb-4">
              <label><strong>Rewards</strong></label>
              <div id="rewards-section" class="mt-2">
                <div class="reward-group border rounded p-2 mb-2">
                  <!-- campi reward esistenti -->
                </div>
              </div>
              <button type="button" class="btn btn-primary mt-2" onclick="addReward()">
                Aggiungi reward
              </button>
            </div>
          </div>
        </div>
        <hr class="my-4">
    
        <button type="submit" class="btn btn-success">Crea progetto</button>
        <a href="/dashboard" class="btn btn-danger">Annulla</a>

        <!-- Modal aggiunta componente -->
        <div class="modal fade" id="addComponentModal" tabindex="-1" role="dialog" aria-labelledby="addComponentModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form id="addComponentForm" method="POST" action="/create-project">
                        <input type="hidden" name="add_component" value="1">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addComponentModalLabel">Aggiungi nuova componente</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="new_component_name">Nome</label>
                                    <input type="text" class="form-control" id="new_component_name" name="new_component_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_component_desc">Descrizione</label>
                                    <textarea class="form-control" id="new_component_desc" name="new_component_desc" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="new_component_price">Prezzo (€)</label>
                                    <input type="number" class="form-control" id="new_component_price" name="new_component_price" min="0.01" step="0.01" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Aggiungi</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <!-- Modal per aggiungere componenti hardware -->
        <div class="modal fade" id="addComponentsModal" tabindex="-1" role="dialog" aria-labelledby="addComponentsModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="addComponentsModalLabel">Aggiungi componenti hardware</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="form-row mb-2">
                  <div class="col-md-6"><strong>Componente</strong></div>
                  <div class="col-md-4"><strong>Quantità</strong></div>
                  <div class="col-md-2"></div>
                </div>
                <div id="modal-components-list">
                  <div class="form-row align-items-end mb-2 component-row">
                    <div class="col-md-6">
                      <select class="form-control" name="component_name[]">
                        <option value="">Seleziona componente</option>
                        <?php foreach ($allComponents as $component): ?>
                          <option value="<?php echo htmlspecialchars($component['nome']); ?>" data-price="<?php echo $component['prezzo']; ?>">
                            <?php echo htmlspecialchars($component['nome']) . " (€" . number_format($component['prezzo'],2,',','.') . ")"; ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <input type="number" class="form-control" name="component_qty[]" min="1" value="1">
                    </div>
                    <div class="col-md-2">
                      <button type="button" class="btn btn-danger remove-modal-component" onclick="removeModalComponentRow(this)">-</button>
                    </div>
                  </div>
                </div>
                <button type="button" class="btn btn-primary mb-2" onclick="addModalComponentRow()">Aggiungi riga</button>
                <button type="button" class="btn btn-secondary mb-2" data-toggle="modal" data-target="#addComponentModal">Nuova componente</button>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-success" id="confirmComponentsBtn">Conferma</button>
              </div>
            </div>
          </div>
        </div>
    </form>
</div>
</body>
</html>
