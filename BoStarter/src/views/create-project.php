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
    <h2>Crea un nuovo progetto</h2>
    <form action="/create-project" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Nome progetto</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="description">Descrizione</label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label for="budget">Budget (€)</label>
            <input type="number" class="form-control" id="budget" name="budget" min="1" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="deadline">Data limite</label>
            <input type="date" class="form-control" id="deadline" name="deadline" required>
        </div>
        <div class="form-group">
            <label for="type">Tipo</label>
            <select class="form-control" id="type" name="type" required onchange="toggleHardwareSection()">
                <option value="HARDWARE">Hardware</option>
                <option value="SOFTWARE">Software</option>
            </select>
        </div>

        <!-- Sezione componenti hardware -->
        <div id="hardware-section" style="display:none;">
            <label><strong>Componenti hardware</strong></label>
            <div id="components-list">
                <div class="form-row align-items-end mb-2 component-row">
                    <div class="col-md-6">
                        <label>Componente</label>
                        <select class="form-control" name="component_name[]">
                            <option value="">Seleziona componente</option>
                            <?php foreach ($allComponents as $component): ?>
                                <option value="<?php echo htmlspecialchars($component['nome']); ?>">
                                    <?php echo htmlspecialchars($component['nome']) . " (€" . number_format($component['prezzo'],2,',','.') . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Quantità</label>
                        <input type="number" class="form-control" name="component_qty[]" min="1" value="1">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-component" onclick="removeComponentRow(this)">-</button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-primary mb-2" onclick="addComponentRow()">Aggiungi componente</button>
            <button type="button" class="btn btn-secondary mb-2" data-toggle="modal" data-target="#addComponentModal">Nuova componente</button>
        </div>

        <div class="form-group">
            <label for="immagini">Foto progetto (puoi caricare più immagini)</label>
            <input type="file" class="form-control-file" id="immagini" name="immagini[]" accept="image/*" multiple required>
        </div>

        <div id="rewards-section">
            <label><strong>Reward</strong></label>
            <div class="reward-group border rounded p-2 mb-2">
                <div class="form-group">
                    <label for="reward_description[]">Descrizione reward</label>
                    <input type="text" class="form-control" name="reward_description[]" required>
                </div>
                <div class="form-group">
                    <label for="reward_image[]">Immagine reward</label>
                    <input type="file" class="form-control-file" name="reward_image[]" accept="image/*" required>
                </div>
                <button type="button" class="btn btn-primary mb-3" onclick="addReward()">Aggiungi reward</button>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Crea progetto</button>
        <a href="/dashboard" class="btn btn-danger">Annulla</a>
    </form>
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
</div>
</body>
</html>
