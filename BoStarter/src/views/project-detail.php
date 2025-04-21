<?php
require_once __DIR__ . '/../controllers/ProjectDetailController.php';
require_once __DIR__ . '/components/navbar.php';
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dettaglio Progetto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @media (min-width: 992px) {
            .project-main-info {
                border-right: 1px solid #eee;
            }
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <?php if ($project): ?>
    <div class="row">
        <!-- Colonna sinistra: info progetto e gallery -->
        <div class="col-lg-8 project-main-info mb-4">
            <h2><?php echo htmlspecialchars($project['nome']); ?></h2>
            <p><strong>Descrizione:</strong> <?php echo nl2br(htmlspecialchars($project['descrizione'])); ?></p>
            <p><strong>Budget:</strong> € <?php echo number_format($project['budget'], 2, ',', '.'); ?></p>
            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($project['tipo']); ?></p>
            <p><strong>Email creatore:</strong> <?php echo htmlspecialchars($project['email_utente_creatore']); ?></p>

            <div class="mb-3">
                <strong>Galleria foto:</strong>
                <div class="d-flex flex-wrap">
                    <?php
                    if ($photos) {
                        foreach ($photos as $img) {
                            echo '<img src="data:image/jpeg;base64,' . base64_encode($img) . '" class="img-thumbnail m-1" style="max-width:120px;max-height:120px; cursor: zoom-in;" alt="Foto progetto" data-toggle="modal" data-target="#imgZoomModal" data-img="data:image/jpeg;base64,' . base64_encode($img) . '">';
                        }
                    } else {
                        echo '<span class="text-muted">Nessuna foto disponibile.</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- Colonna destra: form finanziamento -->
        <div class="col-lg-4 mb-4">
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Finanzia il progetto</h5>
                    <form action="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" method="post">
                        <input type="hidden" name="nome_progetto" value="<?php echo htmlspecialchars($project['nome']); ?>">
                        <div class="form-group">
                            <label for="importo">Importo (€):</label>
                            <input type="number" min="1" step="0.05" class="form-control" name="importo" id="importo" required <?php echo ($hasFundedToday ? 'disabled' : ''); ?>>
                        </div>
                        <?php if (!empty($rewards)): ?>
                        <div class="form-group">
                            <label for="codice_reward">Scegli una reward</label>
                            <select class="form-control" name="codice_reward" id="codice_reward" required onchange="showRewardImage()">
                                <option value="">Seleziona...</option>
                                <?php foreach ($rewards as $idx => $reward): ?>
                                    <option 
                                        value="<?php echo htmlspecialchars($reward['codice']); ?>"
                                        data-img="<?php echo 'data:image/jpeg;base64,' . base64_encode($reward['immagine']); ?>"
                                        data-desc="<?php echo htmlspecialchars($reward['descrizione']); ?>"
                                    >
                                        <?php echo htmlspecialchars($reward['descrizione']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="reward-image-preview" class="mb-3" style="display:none;">
                            <img id="reward-img" src="" alt="Reward" class="img-thumbnail" style="max-width:150px;max-height:150px;">
                            <div id="reward-desc" class="mt-2"></div>
                        </div>
                        <script>
                        function showRewardImage() {
                            var select = document.getElementById('codice_reward');
                            var idx = select.selectedIndex;
                            var option = select.options[idx];
                            var img = option.getAttribute('data-img');
                            var desc = option.getAttribute('data-desc');
                            if (img && select.value) {
                                document.getElementById('reward-img').src = img;
                                document.getElementById('reward-desc').innerText = desc;
                                document.getElementById('reward-image-preview').style.display = 'block';
                            } else {
                                document.getElementById('reward-image-preview').style.display = 'none';
                            }
                        }
                        </script>
                        <?php else: ?>
                            <div class="alert alert-warning">Nessuna reward disponibile per questo progetto.</div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-success btn-block" <?php echo ($hasFundedToday ? 'disabled' : ''); ?>>
                            <?php echo $hasFundedToday ? 'Hai già finanziato oggi' : 'Finanzia'; ?>
                        </button>
                    </form>
                    <!-- Chip stato e raccolta -->
                    <div class="mt-4">
                        <span class="badge badge-<?php echo (isset($project['stato']) && $project['stato'] === 'APERTO') ? 'success' : 'secondary'; ?>" style="font-size:1rem;">
                            <?php
                            if (isset($project['stato']) && $project['stato']) {
                                echo ucfirst(strtolower($project['stato']));
                            } else {
                                echo 'Stato sconosciuto';
                            }
                            ?>
                        </span>
                        <span class="badge badge-info ml-2" style="font-size:1rem;">
                            Raccolti: € 
                            <?php
                            if (isset($project['somma_raccolta'])) {
                                echo number_format($project['somma_raccolta'], 2, ',', '.');
                            } else {
                                // Se non hai la colonna somma_raccolta, puoi calcolarla con una query/stored oppure mostrare 0,00
                                echo "0,00";
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Commenti e form commento -->
    <div class="row">
        <div class="col-lg-8">
            <div class="mb-3">
                <strong>Commenti:</strong>
                <ul class="list-group">
                    <?php
                    if ($comments) {
                        foreach ($comments as $comment) {
                            echo '<li class="list-group-item">';
                            echo '<strong>' . htmlspecialchars($comment['nickname']) . ':</strong> ' . htmlspecialchars($comment['testo']);
                            echo '<br><small class="text-muted">' . htmlspecialchars($comment['data']) . '</small>';
                            if (!empty($comment['risposta'])) {
                                echo '<div class="mt-2 ml-3 p-2 bg-light border rounded"><strong>Creatore:</strong> ' . htmlspecialchars($comment['risposta']) . '</div>';
                            }
                            if (
                                isset($_SESSION['user_id']) &&
                                $_SESSION['user_id'] === $project['email_utente_creatore'] &&
                                empty($comment['risposta'])
                            ) {
                                echo '
                                <button class="btn btn-sm btn-outline-primary mt-2" type="button" data-toggle="collapse" data-target="#replyForm' . $comment['id'] . '">Rispondi</button>
                                <div class="collapse mt-2" id="replyForm' . $comment['id'] . '">
                                    <form action="/project-detail?nome=' . urlencode($project['nome']) . '" method="post">
                                        <input type="hidden" name="id_commento" value="' . htmlspecialchars($comment['id']) . '">
                                        <input type="hidden" name="nome_progetto" value="' . htmlspecialchars($project['nome']) . '">
                                        <div class="form-group mb-1">
                                            <textarea class="form-control form-control-sm" name="testo_risposta" rows="1" placeholder="Rispondi..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary">Invia risposta</button>
                                    </form>
                                </div>
                                ';
                            }
                            echo '</li>';
                        }
                    } else {
                        echo '<li class="list-group-item text-muted">Nessun commento.</li>';
                    }
                    ?>
                </ul>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Lascia un commento</h5>
                    <form action="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" method="post">
                        <input type="hidden" name="nome_progetto" value="<?php echo htmlspecialchars($project['nome']); ?>">
                        <div class="form-group">
                            <label for="testo_commento_<?php echo md5($project['nome']); ?>">Commento:</label>
                            <textarea class="form-control" id="testo_commento_<?php echo md5($project['nome']); ?>" name="testo_commento" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Invia commento</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <a href="/dashboard" class="btn btn-secondary mt-3">Torna alla Dashboard</a>
        </div>
    </div>

    <!-- Modal per zoom immagini -->
    <div class="modal fade" id="imgZoomModal" tabindex="-1" role="dialog" aria-labelledby="imgZoomModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content bg-transparent border-0">
          <div class="modal-body text-center p-0">
            <img src="" id="imgZoomModalImg" class="img-fluid rounded" alt="Zoom immagine">
          </div>
        </div>
      </div>
    </div>

    <?php else: ?>
        <div class="alert alert-danger">Progetto non trovato.</div>
        <a href="/dashboard" class="btn btn-secondary">Torna alla Dashboard</a>
    <?php endif; ?>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var zoomImgs = document.querySelectorAll('.img-thumbnail[data-toggle="modal"]');
    var modalImg = document.getElementById('imgZoomModalImg');
    zoomImgs.forEach(function(img) {
        img.addEventListener('click', function() {
            modalImg.src = this.getAttribute('data-img');
        });
    });
    $('#imgZoomModal').on('hidden.bs.modal', function () {
        modalImg.src = '';
    });
});
</script>
</body>
</html>
