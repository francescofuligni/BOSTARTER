<?php
if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

if (!isset($_SESSION['user_id']) || !$user->isCreator($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit;
}

require_once __DIR__ . '/../controllers/CreateProjectController.php';
require_once __DIR__ . '/components/navbar.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Crea nuovo progetto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-5">
      <h2>Crea un nuovo progetto</h2>
      <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
      <?php endif; ?>
      <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
      <?php endif; ?>
      <form action="/create-project" method="post" enctype="multipart/form-data">
          <div class="form-group">
              <label for="nome">Nome progetto</label>
              <input type="text" class="form-control" id="nome" name="nome" required>
          </div>
          <div class="form-group">
              <label for="descrizione">Descrizione</label>
              <textarea class="form-control" id="descrizione" name="descrizione" rows="4" required></textarea>
          </div>
          <div class="form-group">
              <label for="budget">Budget (€)</label>
              <input type="number" class="form-control" id="budget" name="budget" min="1" step="0.01" required>
          </div>
          <div class="form-group">
              <label for="data_limite">Data limite</label>
              <input type="date" class="form-control" id="data_limite" name="data_limite" required>
          </div>
          <div class="form-group">
              <label for="tipo">Tipo</label>
              <select class="form-control" id="tipo" name="tipo" required>
                  <option value="HARDWARE">Hardware</option>
                  <option value="SOFTWARE">Software</option>
              </select>
          </div>
          <div class="form-group">
              <label for="immagini">Foto progetto (puoi caricare più immagini)</label>
              <input type="file" class="form-control-file" id="immagini" name="immagini[]" accept="image/*" multiple required>
          </div>

          <div id="rewards-section">
              <label><strong>Reward</strong></label>
              <div class="reward-group border rounded p-2 mb-2">
                  <div class="form-group">
                      <label for="reward_codice[]">Codice reward</label>
                      <input type="text" class="form-control" name="reward_codice[]" required>
                  </div>
                  <div class="form-group">
                      <label for="reward_descrizione[]">Descrizione reward</label>
                      <input type="text" class="form-control" name="reward_descrizione[]" required>
                  </div>
                  <div class="form-group">
                      <label for="reward_immagine[]">Immagine reward</label>
                      <input type="file" class="form-control-file" name="reward_immagine[]" accept="image/*" required>
                  </div>
              </div>
          </div>
          <button type="button" class="btn btn-secondary mb-3" onclick="addReward()">Aggiungi reward</button>

          <button type="submit" class="btn btn-primary">Crea progetto</button>
          <a href="/dashboard" class="btn btn-secondary">Annulla</a>
      </form>
  </div>
  <script>
  function addReward() {
      const section = document.getElementById('rewards-section');
      const group = section.querySelector('.reward-group').cloneNode(true);
      group.querySelectorAll('input').forEach(input => input.value = '');
      section.appendChild(group);
  }
  </script>
</body>
</html>