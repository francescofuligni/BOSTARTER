<?php
require_once __DIR__ . '/../controllers/CreateProjectController.php';
require_once __DIR__ . '/components/navbar.php';
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Crea nuovo progetto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
            <select class="form-control" id="type" name="type" required>
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
</div>
</body>
</html>
