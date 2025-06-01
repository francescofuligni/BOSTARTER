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
    <link rel="stylesheet" href="/style/project-detail.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="/js/project-detail.js"></script>
</head>
<body>
    <div class="container mt-5">
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

        <?php if ($project): ?>
        <h1 class="display-4 font-weight-bold text-dark mb-4"><?php echo htmlspecialchars($project['nome']); ?></h1>
        <div class="row">

            <!-- Colonna sinistra: info progetto e gallery -->
            <div class="col-lg-8 project-main-info mb-4">
                <p><strong>Creatore:</strong> <?php echo htmlspecialchars($project['email_utente_creatore']); ?></p>
                <p><strong>Descrizione:</strong> <?php echo nl2br(htmlspecialchars($project['descrizione'])); ?></p>
                <p><strong>Tipo progetto:</strong> <?php echo htmlspecialchars($project['tipo']); ?></p>
                <p><strong>Stato:</strong> 
                    <span class="badge badge-<?php echo ($project['stato'] === 'APERTO' ? 'success' : 'secondary'); ?>" style="font-size:1rem;">
                        <?php echo ucfirst(strtolower($project['stato'])); ?>
                    </span>
                </p>
                <p><strong>Data creazione:</strong> <?php echo isset($project['data_inserimento']) ? date('d/m/Y', strtotime($project['data_inserimento'])) : ''; ?></p>
                <p><strong>Data limite:</strong> <?php echo date('d/m/Y', strtotime($project['data_limite'])); ?></p>
                <div class="mb-3">
                    <strong>Galleria del progetto:</strong>
                    <div class="d-flex flex-wrap">
                        <?php
                        if ($photos) {
                            foreach ($photos as $img) {
                                echo '<img src="data:image/jpeg;base64,' . base64_encode($img) . '" class="img-thumbnail m-1 gallery-img" alt="Foto progetto" data-toggle="modal" data-target="#imgZoomModal" data-img="data:image/jpeg;base64,' . base64_encode($img) . '">';
                            }
                        } else {
                            echo '<span class="text-muted">Nessuna foto disponibile.</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Colonna destra: budget e finanziamento -->
            <div class="col-lg-4 mb-4">
                <div class="text-center">
                <?php
                $raccolti = isset($project['somma_raccolta']) ? $project['somma_raccolta'] : 0.00;
                $budget = isset($project['budget']) ? $project['budget'] : 0.00;
                $progress = ($budget > 0) ? min(100, ($raccolti / $budget) * 100) : 0;
                ?>
                <svg width="100%" height="180" viewBox="0 0 36 36" class="circular-chart">
                    <path class="circle-bg"
                        d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"
                        fill="none"
                        stroke="#eee"
                        stroke-width="2"/>
                    <path class="circle"
                        stroke-dasharray="<?php echo $progress; ?>, 100"
                        d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"
                        fill="none"
                        stroke="#28a745"
                        stroke-width="2"
                        stroke-linecap="round"/>
                    <text x="18.75" y="18.75" class="percentage" text-anchor="middle"><?php echo number_format($progress, 0); ?>%</text>
                </svg>
                <div class="mb-3 ring-label">
                    € <?php echo number_format($raccolti, 2, ',', '.'); ?> raccolti
                    su <strong>€ <?php echo number_format($budget, 2, ',', '.'); ?></strong>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                        // Disabilita il bottone se utente ha già finanziato oggi o progetto chiuso
                        $isClosed = isset($project['stato']) && $project['stato'] === 'CHIUSO';
                        $disableFundButton = $hasFundedToday || $isClosed;
                    ?>
                    <button 
                        class="btn btn-success btn-block" 
                        data-toggle="modal" 
                        data-target="#fundModal" 
                        <?php echo $disableFundButton ? 'disabled' : ''; ?>
                    >
                        <?php 
                            if ($isClosed) {
                                echo 'Progetto chiuso';
                            } elseif ($hasFundedToday) {
                                echo 'Hai già finanziato il progetto oggi';
                            } else {
                                echo 'Finanzia il progetto';
                            }
                        ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        </div>

        <div class="container mt-5">

        <!-- Sezione componenti hardware -->
        <?php if ($project['tipo'] === 'HARDWARE' && !empty($components)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <h3 class="mt-3 mb-3">Componenti hardware</h3>
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
                </div>
            </div>
        <?php endif; ?>

        <!-- Sezione profili richiesti -->
        <?php if ($project['tipo'] === 'SOFTWARE'): ?>
        <div class="row mt-4">
            <div class="col-12">
                <h3 class="mt-3 mb-3">Profili</h3>
                <?php if (empty($profiles)): ?>
                    <div class="alert alert-info">Nessun profilo richiesto per questo progetto.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($profiles as $profile): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 shadow-sm" style="font-size: 0.9rem;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($profile['nome']); ?></h5>
                                        <span class="badge badge-<?php echo ($profile['stato'] === 'DISPONIBILE') ? 'success' : 'secondary'; ?>">
                                            <?php echo htmlspecialchars($profile['stato']); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($profile['skills'])): ?>
                                            <p class="text-muted">Nessuna competenza specifica richiesta.</p>
                                        <?php else: ?>
                                            <ul class="list-group mb-2">
                                                <?php foreach ($profile['skills'] as $skill): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <?php echo htmlspecialchars($skill['nome_competenza']); ?>
                                                        <span class="badge badge-primary rounded-circle d-flex align-items-center justify-content-center skill-badge">
                                                            <?php echo htmlspecialchars($skill['livello']); ?>
                                                        </span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>

                                        <!-- Mostra candidature -->
                                        <?php if ($isCreator): ?>
                                            <h6 class="mt-3">Candidature</h6>
                                            <?php if (empty($profile['applications'])): ?>
                                                <p class="text-muted">Nessuna candidatura ricevuta.</p>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 50%;">Utente</th>
                                                                <th>Stato</th>
                                                                <th>Azioni</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($profile['applications'] as $app): ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($app['nickname']); ?></td>
                                                                    <td>
                                                                        <?php
                                                                            $stato = $app['stato'];
                                                                            $color = ($stato === 'ACCETTATA') ? 'green' : (($stato === 'RIFIUTATA') ? 'red' : 'gray');
                                                                            $label = ucfirst(strtolower($stato));
                                                                        ?>
                                                                        <span class="application-status-dot" style="background-color: <?php echo $color; ?>;"></span>
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
                                                                        <?php else: ?>
                                                                            <span class="text-muted">-</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif (isset($_SESSION['user_id'])): ?>

                                            <!-- Mostra bottone per candidarsi -->
                                            <?php if ($profile['stato'] === 'DISPONIBILE'): ?>
                                                <?php if (!empty($profile['has_applied'])): ?>
                                                    <div class="alert alert-info mt-3">Hai già inviato una candidatura per questo profilo.</div>
                                                <?php else: ?>
                                                    <form action="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" method="post" class="mt-2">
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
                <?php if ($isCreator): ?>
                    <button class="btn btn-outline-primary mb-4" data-toggle="modal" data-target="#createProfileModal">Crea un nuovo profilo</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sezione commenti e form commento -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="mb-3">
                    <h3 class="mt-3 mb-3">Commenti</h3>
                    <ul class="list-group">
                        <?php if ($comments): ?>
                            <?php foreach ($comments as $comment): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <strong><?= htmlspecialchars($comment['nickname']) ?>:</strong> <?= htmlspecialchars($comment['testo']) ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($comment['data']) ?></small>
                                            <?php if (!empty($comment['risposta'])): ?>
                                                <div class="mt-2 ml-3 p-2 bg-light border rounded">
                                                    <strong>Creatore:</strong> <?= htmlspecialchars($comment['risposta']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <div class="ml-2">
                                                <?php 
                                                
                                                $canDelete = ($_SESSION['user_id'] === $project['email_utente_creatore']) || 
                                                           (isset($comment['email_utente']) && $_SESSION['user_id'] === $comment['email_utente']);
                                                ?>
                                                
                                                <?php if ($canDelete): ?>   
                                                    <button class="btn btn-sm btn-outline-danger" type="button" data-toggle="modal" data-target="#deleteModal<?= $comment['id'] ?>">
                                                        <span>Elimina 
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                                                </svg>
                                                        </span>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($_SESSION['user_id'] === $project['email_utente_creatore'] && empty($comment['risposta'])): ?>
                                                    <button class="btn btn-sm btn-outline-primary ml-1" type="button" data-toggle="modal" data-target="#replyModal<?= $comment['id'] ?>">Rispondi</button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                
                                <!-- Modal per conferma eliminazione commento -->
                                <?php if (isset($_SESSION['user_id']) && $canDelete): ?>
                                    <div class="modal fade" id="deleteModal<?= $comment['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?= $comment['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <form action="/project-detail?nome=<?= urlencode($project['nome']) ?>" method="post">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel<?= $comment['id'] ?>">Conferma eliminazione</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_commento" value="<?= htmlspecialchars($comment['id']) ?>">
                                                        <input type="hidden" name="nome_progetto" value="<?= htmlspecialchars($project['nome']) ?>">
                                                        <input type="hidden" name="rimuovi_commento" value="1">
                                                        <p>Sei sicuro di voler eliminare questo commento? Questa azione non può essere annullata.</p>
                                                        <?php if (!empty($comment['risposta'])): ?>
                                                            <p class="text-warning"><strong>Attenzione:</strong> Eliminando questo commento verrà rimossa anche la risposta del creatore.</p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                                        <button type="submit" class="btn btn-danger">Elimina commento</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Modal per risposta (existing code) -->
                                <?php if (
                                    isset($_SESSION['user_id']) &&
                                    $_SESSION['user_id'] === $project['email_utente_creatore'] &&
                                    empty($comment['risposta'])
                                ): ?>
                                    <div class="modal fade" id="replyModal<?= $comment['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel<?= $comment['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <form action="/project-detail?nome=<?= urlencode($project['nome']) ?>" method="post">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="replyModalLabel<?= $comment['id'] ?>">Rispondi a <?= htmlspecialchars($comment['nickname']) ?></h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_commento" value="<?= htmlspecialchars($comment['id']) ?>">
                                                        <input type="hidden" name="nome_progetto" value="<?= htmlspecialchars($project['nome']) ?>">
                                                        <div class="form-group">
                                                            <textarea class="form-control" name="testo_risposta" rows="3" placeholder="Scrivi la tua risposta..." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Invia risposta</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted">Nessun commento.</li>
                        <?php endif; ?>
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
                        <div class="mb-2"><strong>Lascia un commento</strong></div>
                        <form action="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" method="post">
                            <input type="hidden" name="nome_progetto" value="<?php echo htmlspecialchars($project['nome']); ?>">
                            <div class="form-group">
                                <textarea class="form-control" placeholder="Scrivi il tuo commento..." id="testo_commento_<?php echo md5($project['nome']); ?>" name="testo_commento" rows="2" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Invia commento</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
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

        <!-- Modale finanziamento progetto -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="modal fade" id="fundModal" tabindex="-1" role="dialog" aria-labelledby="fundModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" method="post">
                        <div class="modal-header">
                            <h5 class="modal-title" id="fundModalLabel">Finanzia il progetto</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="nome_progetto" value="<?php echo htmlspecialchars($project['nome']); ?>">
                            <div class="form-group">
                                <label for="importo">Importo (€)</label>
                                <input type="number" min="1" step="0.05" class="form-control" name="importo" id="importo" required <?php echo ($hasFundedToday ? 'disabled' : ''); ?>>
                            </div>
                            <?php if (!empty($rewards)): ?>
                            <div class="form-group">
                                <label for="codice_reward">Reward</label>
                                <select id="codice_reward" class="form-control" name="codice_reward" required onchange="showRewardImage()">
                                    <option value="">Scegli una reward</option>
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
                                <img id="reward-img" src="" alt="Reward" class="img-thumbnail reward-img" data-toggle="modal" data-target="#imgZoomModal" data-img="">
                                <div id="reward-desc" class="mt-2"></div>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning">Nessuna reward disponibile per questo progetto.</div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                            <?php 
                                $isClosed = isset($project['stato']) && $project['stato'] === 'CHIUSO';
                            ?>
                            <button 
                                type="submit" 
                                class="btn btn-success" 
                                <?php echo ($hasFundedToday || $isClosed) ? 'disabled' : ''; ?>
                            >
                                <?php 
                                    if ($isClosed) {
                                        echo 'Progetto chiuso';
                                    } elseif ($hasFundedToday) {
                                        echo 'Hai già finanziato oggi';
                                    } else {
                                        echo 'Conferma finanziamento';
                                    }
                                ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Modale per creazione profilo -->
        <?php if ($isCreator): ?>
            <div class="modal fade" id="createProfileModal" tabindex="-1" role="dialog" aria-labelledby="createProfileModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createProfileModalLabel">Crea un nuovo profilo</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Chiudi">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="/project-detail?nome=<?php echo urlencode($project['nome']); ?>" method="post">
                                <input type="hidden" name="project_name" value="<?php echo htmlspecialchars($project['nome']); ?>">
                                <div class="form-group">
                                    <label for="profile_name">Nome del profilo</label>
                                    <input type="text" class="form-control" id="profile_name" name="profile_name" required>
                                </div>
                                <div id="skills-container">
                                    <div class="skill-row mb-3">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <label>Competenza richiesta</label>
                                                <select class="form-control" name="skill_name[]">
                                                    <option value="">Seleziona competenza</option>
                                                    <?php foreach ($allCompetences as $competence): ?>
                                                        <option value="<?php echo htmlspecialchars($competence['nome']); ?>">
                                                            <?php echo htmlspecialchars($competence['nome']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Livello (0-5)</label>
                                                <select class="form-control" name="skill_level[]">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-secondary" id="add-skill">Aggiungi competenza</button>
                                <button type="submit" class="btn btn-primary">Crea profilo</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-danger">Progetto non trovato</div>
            <a href="/dashboard" class="btn btn-secondary">Torna alla Dashboard</a>
        <?php endif; ?>
    </div>
</body>
</html>
