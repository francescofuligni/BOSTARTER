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

            <?php if ($project['tipo'] === 'HARDWARE' && !empty($components)): ?>
                <h4 class="mt-4">Componenti hardware del progetto</h4>
                <ul class="list-group mb-4">
                    <?php foreach ($components as $comp): ?>
                        <li class="list-group-item">
                            <strong><?php echo htmlspecialchars($comp['nome']); ?></strong>
                            (<?php echo htmlspecialchars($comp['quantita']); ?> x €<?php echo number_format($comp['prezzo'], 2, ',', '.'); ?>)
                            <br>
                            <small><?php echo htmlspecialchars($comp['descrizione']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <!-- Colonna destra: form finanziamento -->
        <div class="col-lg-4 mb-4">
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($_SESSION['error']); ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($_SESSION['success']); ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
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
                            <select id="codice_reward" class="form-control" name="codice_reward" required onchange="showRewardImage()">
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

    <?php if ($project['tipo'] === 'SOFTWARE'): ?>
    <div class="row mt-4">
        <div class="col-12">
            <h3>Profili richiesti</h3>
            
            <?php if (empty($profiles)): ?>
                <div class="alert alert-info">Nessun profilo richiesto per questo progetto.</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($profiles as $profile): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($profile['nome']); ?></h5>
                                    <span class="badge badge-<?php echo ($profile['stato'] === 'DISPONIBILE') ? 'success' : 'secondary'; ?>">
                                        <?php echo htmlspecialchars($profile['stato']); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <h6>Competenze richieste:</h6>
                                    <?php if (empty($profile['skills'])): ?>
                                        <p class="text-muted">Nessuna competenza specifica richiesta.</p>
                                    <?php else: ?>
                                        <ul class="list-group mb-3">
                                            <?php foreach ($profile['skills'] as $skill): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <?php echo htmlspecialchars($skill['nome_competenza']); ?>
                                                    <span class="badge badge-primary badge-pill">
                                                        Livello <?php echo htmlspecialchars($skill['livello']); ?>/5
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    
                                    <?php if ($isCreator): ?>
                                        <!-- Creator: Show applications -->
                                        <h6>Candidature:</h6>
                                        <?php if (empty($profile['applications'])): ?>
                                            <p class="text-muted">Nessuna candidatura ricevuta.</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Utente</th>
                                                            <th>Stato</th>
                                                            <th>Azioni</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($profile['applications'] as $app): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($app['nickname']); ?></td>
                                                                <td>
                                                                    <span class="badge badge-<?php 
                                                                        echo ($app['stato'] === 'ACCETTATA') ? 'success' : 
                                                                            (($app['stato'] === 'RIFIUTATA') ? 'danger' : 'warning'); 
                                                                    ?>">
                                                                        <?php echo htmlspecialchars($app['stato']); ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <?php if ($app['stato'] === 'ATTESA'): ?>
                                                                        <form action="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" method="post" class="d-inline">
                                                                            <input type="hidden" name="applicant_email" value="<?php echo htmlspecialchars($app['email_utente']); ?>">
                                                                            <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($profile['id']); ?>">
                                                                            <input type="hidden" name="project_name" value="<?php echo htmlspecialchars($project['nome']); ?>">
                                                                            <input type="hidden" name="status" value="ACCETTATA">
                                                                            <button type="submit" class="btn btn-sm btn-success">Accetta</button>
                                                                        </form>
                                                                        <form action="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" method="post" class="d-inline ml-1">
                                                                            <input type="hidden" name="applicant_email" value="<?php echo htmlspecialchars($app['email_utente']); ?>">
                                                                            <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($profile['id']); ?>">
                                                                            <input type="hidden" name="project_name" value="<?php echo htmlspecialchars($project['nome']); ?>">
                                                                            <input type="hidden" name="status" value="RIFIUTATA">
                                                                            <button type="submit" class="btn btn-sm btn-danger">Rifiuta</button>
                                                                        </form>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Form to add required skills -->
                                        <button class="btn btn-sm btn-outline-primary mt-3" type="button" data-toggle="collapse" 
                                                data-target="#addSkillForm<?php echo $profile['id']; ?>">
                                            Aggiungi competenza richiesta
                                        </button>
                                        <div class="collapse mt-2" id="addSkillForm<?php echo $profile['id']; ?>">
                                            <form action="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" method="post" class="card card-body">
                                                <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($profile['id']); ?>">
                                                <input type="hidden" name="project_name" value="<?php echo htmlspecialchars($project['nome']); ?>">
                                                <div class="form-group">
                                                    <label for="skill_name_<?php echo $profile['id']; ?>">Competenza:</label>
                                                    <select class="form-control form-control-sm" id="skill_name_<?php echo $profile['id']; ?>" name="skill_name" required>
                                                        <option value="">Seleziona competenza...</option>
                                                        <?php foreach ($allCompetences as $competence): ?>
                                                            <option value="<?php echo htmlspecialchars($competence['nome']); ?>">
                                                                <?php echo htmlspecialchars($competence['nome']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="skill_level_<?php echo $profile['id']; ?>">Livello richiesto:</label>
                                                    <select class="form-control form-control-sm" id="skill_level_<?php echo $profile['id']; ?>" name="skill_level" required>
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?>/5</option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-primary">Aggiungi</button>
                                            </form>
                                        </div>
                                    <?php elseif (isset($_SESSION['user_id'])): ?>
                                        <!-- User: Show apply button -->
                                        <?php if ($profile['stato'] === 'DISPONIBILE'): ?>
                                            <?php if (!empty($profile['has_applied'])): ?>
                                                <div class="alert alert-info mt-3">Hai già inviato una candidatura per questo profilo.</div>
                                            <?php else: ?>
                                                <form action="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" method="post">
                                                    <input type="hidden" name="profile_id" value="<?php echo htmlspecialchars($profile['id']); ?>">
                                                    <input type="hidden" name="project_name" value="<?php echo htmlspecialchars($project['nome']); ?>">
                                                    <input type="hidden" name="apply" value="1">
                                                    <button type="submit" class="btn btn-primary mt-3">Candidati per questo profilo</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="alert alert-secondary mt-3">Questo profilo non è più disponibile.</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Creator: Form to create new profile -->
            <?php if ($isCreator): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Crea un nuovo profilo</h5>
                    </div>
                    <div class="card-body">
                        <form action="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" method="post">
                            <input type="hidden" name="project_name" value="<?php echo htmlspecialchars($project['nome']); ?>">
                            <div class="form-group">
                                <label for="profile_name">Nome del profilo:</label>
                                <input type="text" class="form-control" id="profile_name" name="profile_name" required>
                            </div>
                            
                            <div id="skills-container">
                                <h6>Competenze richieste:</h6>
                                <div class="skill-row mb-3">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <select class="form-control" name="skill_name[]">
                                                <option value="">Seleziona competenza...</option>
                                                <?php foreach ($allCompetences as $competence): ?>
                                                    <option value="<?php echo htmlspecialchars($competence['nome']); ?>">
                                                        <?php echo htmlspecialchars($competence['nome']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-control" name="skill_level[]">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?>/5</option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-outline-secondary mb-3" id="add-skill">Aggiungi altra competenza</button>
                            <button type="submit" class="btn btn-primary">Crea profilo</button>
                        </form>
                    </div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.getElementById('add-skill').addEventListener('click', function() {
                            const container = document.getElementById('skills-container');
                            const skillRow = document.querySelector('.skill-row').cloneNode(true);
                            container.appendChild(skillRow);
                        });
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

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
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($_SESSION['error']); ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($_SESSION['success']); ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
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
<script src="/js/project-detail.js"></script>
</body>
</html>
