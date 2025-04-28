<?php

/**
 * Classe per la gestione dei progetti.
 * Fornisce metodi per recuperare dettagli, immagini, commenti e altre informazioni sui progetti.
 */
class Project {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }


    /**
     * Recupera tutti i progetti aperti dalla vista dedicata.
     *
     * @return array Array di progetti aperti.
     */
    public function getOpenProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_aperti");
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }


    /**
     * Recupera tutte le immagini associate a un progetto.
     *
     * @param string $projectName Nome del progetto.
     * @return array Array di immagini del progetto.
     */
    public function getPhotos($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT immagine FROM foto_progetto WHERE nome_progetto = :nome_progetto");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_COLUMN)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }


    /**
     * Recupera tutti i commenti relativi a un progetto.
     *
     * @param string $projectName Nome del progetto.
     * @return array Array di commenti del progetto.
     */
    public function getComments($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT id, testo, nickname, data, risposta FROM commenti_progetto WHERE nome_progetto = :nome_progetto ORDER BY data DESC");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }


    /**
     * Ottiene i dettagli di un progetto dalla vista progetti_con_foto.
     *
     * @param string $projectName Nome del progetto.
     * @return array ['success' => bool, 'data' => array|null] Dettagli del progetto o null in caso di errore.
     */
    private function getDetails($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto WHERE nome = :nome");
            $stmt->bindParam(':nome', $projectName);
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => null];
        }
    }


    /**
     * Restituisce dettagli, immagini e commenti di un progetto.
     *
     * @param Project $projectModel Modello progetto da cui chiamare i metodi.
     * @param string $projectName Nome del progetto.
     * @return array Array contenente dettagli, immagini e commenti del progetto.
     */
    function getProjectDetailData($projectModel, $projectName) {
        $project = $this->getDetails($projectName);
        $photos = $this->getPhotos($projectName);
        $comments = $this->getComments($projectName);
        return [
            'success' => $project['success'] && $photos['success'] && $comments['success'],
            'data' => [
                'project' => $project['data'],
                'photos' => $photos['data'],
                'comments' => $comments['data']
            ]
        ];
    }


    /**
     * Recupera tutti i progetti con la prima foto associata.
     *
     * @return array Array di progetti con foto.
     */
    public function getAllProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto");
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }
    

    /**
     * Recupera tutte le ricompense associate a un progetto.
     *
     * @param string $projectName Nome del progetto.
     * @return array Array di ricompense del progetto.
     */
    public function getRewards($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT descrizione, immagine FROM REWARD WHERE nome_progetto = :nome");
            $stmt->bindParam(':nome', $projectName);
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }


    /**
     * Crea un nuovo progetto (solo creatori).
     *
     * @param string $name Nome del progetto.
     * @param string $desc Descrizione del progetto.
     * @param float $budget Budget previsto.
     * @param string $maxDate Data limite.
     * @param string $type Tipo di progetto.
     * @param string $creatorEmail Email del creatore.
     * @param array $rewards Array di ricompense, ciascuna ['image' => ..., 'desc' => ...].
     * @return array ['success' => bool]
     *               Dove 'success' indica l'esito della creazione.
     */
    public function create($name, $desc, $budget, $maxDate, $type, $creatorEmail, $rewards, $photos) {
        try {
            if (empty($rewards) || empty($photos)) {
                return ['success' => false];
            }
            
            // Inizio transazione
            $this->conn->beginTransaction();

            // Inserimento progetto
            $stmt = $this->conn->prepare("CALL crea_progetto(:nome, :descrizione, :budget, :data_limite, :tipo, :email_creatore, @esito)");
            $stmt->bindParam(':nome', $name);
            $stmt->bindParam(':descrizione', $desc);
            $stmt->bindParam(':budget', $budget);
            $stmt->bindParam(':data_limite', $maxDate);
            $stmt->bindParam(':tipo', $type);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $result = $stmt->execute();
            $resultEsito = $this->conn->query("SELECT @esito as esito")->fetch(PDO::FETCH_ASSOC);
            if (!$resultEsito || !$resultEsito['esito']) {
                $this->conn->rollBack();
                return ['success' => false];
            }

            // Inserimento rewards
            foreach ($rewards as $reward) {
                $resultReward = $this->addReward($reward['image'], $reward['desc'], $name, $creatorEmail);
                if (!$resultReward['success']) {
                    error_log('Errore reward, rollback...');
                    $this->conn->rollBack();
                    return ['success' => false];
                }
            }

            // Inserimento foto
            foreach ($photos as $photo) {
                $resultPhoto = $this->addPhoto($name, $photo);
                if (!$resultPhoto['success']) {
                    error_log('Errore foto, rollback...');
                    $this->conn->rollBack();
                    return ['success' => false];
                }
            }

            // Tutto OK
            $this->conn->commit();
            $this->logger->log("Nuovo progetto creato", [
                'nome_progetto' => $name,
                'descrizione' => $desc,
                'budget' => $budget,
                'data_limite' => $maxDate,
                'tipo' => $type,
                'email_creatore' => $creatorEmail
            ]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false];
        }
    }


    /**
     * Aggiunge un commento a un progetto.
     *
     * @param string $projectName Nome del progetto.
     * @param string $userEmail Email dell'utente.
     * @param string $text Testo del commento.
     * @return array ['success' => bool]
     *               Dove 'success' indica l'esito dell'inserimento.
     */
    public function addComment($projectName, $userEmail, $text) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_commento(:nome_progetto, :email_utente, :testo)");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->bindParam(':testo', $text);
            $result = $stmt->execute();
            if ($result) {
                $this->logger->log("Nuovo commento inserito", [
                    'nome_progetto' => $projectName,
                    'email_utente' => $userEmail,
                    'testo' => $text
                ]);
            }
            return ['success' => $result];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }


    /**
     * Aggiunge una risposta a un commento (solo creatore del progetto).
     *
     * @param int $commentId ID del commento.
     * @param string $text Testo della risposta.
     * @param string $creatorEmail Email del creatore.
     * @return array ['success' => bool]
     *               Dove 'success' indica l'esito dell'inserimento.
     */
    public function addReply($commentId, $text, $creatorEmail) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_risposta(:id_commento, :testo, :email_creatore, @is_creatore_progetto)");
            $stmt->bindParam(':id_commento', $commentId);
            $stmt->bindParam(':testo', $text);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $result = $stmt->execute();
            $resultEsito = $this->conn->query("SELECT @is_creatore_progetto as is_creatore_progetto")->fetch(PDO::FETCH_ASSOC);
            if (!$resultEsito || !$resultEsito['is_creatore_progetto']) {
                return ['success' => false];
            }
            if ($result) {
                $this->logger->log("Nuova risposta inserita", [
                    'id_commento' => $commentId,
                    'testo' => $text,
                    'email_creatore' => $creatorEmail
                ]);
            }
            return ['success' => $result];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }


    /**
     * Aggiunge una ricompensa a un progetto (solo creatori).
     *
     * @param string $code Codice della ricompensa.
     * @param string $image Immagine della ricompensa.
     * @param string $desc Descrizione della ricompensa.
     * @param string $projectName Nome del progetto.
     * @param string $creatorEmail Email del creatore.
     * @return array ['success' => bool]
     *               Dove 'success' indica l'esito dell'inserimento.
     */
    public function addReward($image, $desc, $projectName, $creatorEmail) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_reward(:immagine, :descrizione, :nome_progetto, :email_creatore, @esito)");
            $stmt->bindParam(':immagine', $image, PDO::PARAM_LOB);
            $stmt->bindParam(':descrizione', $desc);
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $result = $stmt->execute();
            $resultEsito = $this->conn->query("SELECT @esito as esito")->fetch(PDO::FETCH_ASSOC);
            if (!$resultEsito || !$resultEsito['esito']) {
                return ['success' => false];
            }
            if ($result) {
                $this->logger->log("Nuova ricompensa aggiunta", [
                    'nome_progetto' => $projectName,
                    'email_creatore' => $creatorEmail,
                    'descrizione' => $desc
                ]);
            }
            return ['success' => $result];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }


    /**
     * Esegue il finanziamento di un progetto e associa una ricompensa.
     *
     * @param string $projectName Nome del progetto.
     * @param float $amount Importo del finanziamento.
     * @param string $userEmail Email dell'utente.
     * @param string $rewardCode Codice della ricompensa.
     * @return array ['success' => bool]
     *               Dove 'success' indica l'esito del finanziamento.
     */
    public function fund($projectName, $amount, $userEmail, $rewardCode) {
        try {
            $stmt = $this->conn->prepare("CALL finanzia_progetto(:email_utente, :nome_progetto, :importo, @is_progetto_aperto)");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':importo', $amount);
            if ($stmt->execute()) {
                $result = $this->conn->query("SELECT @is_progetto_aperto as is_progetto_aperto")->fetch(PDO::FETCH_ASSOC);
                if (!$result || !$result['is_progetto_aperto']) {
                    return ['success' => false];
                }
                $stmt2 = $this->conn->prepare("CALL scegli_reward(:email_utente, :nome_progetto, :codice_reward)");
                $stmt2->bindParam(':email_utente', $userEmail);
                $stmt2->bindParam(':nome_progetto', $projectName);
                $stmt2->bindParam(':codice_reward', $rewardCode);
                $stmt2->execute();
                $this->logger->log("Nuovo finanziamento", [
                    'nome_progetto' => $projectName,
                    'email_utente' => $userEmail,
                    'importo' => $amount,
                    'codice_reward' => $rewardCode
                ]);
                return ['success' => true];
            } else {
                return ['success' => false];
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }


    /**
     * Aggiunge una foto a un progetto nel database.
     *
     * @param string $nome_progetto Nome del progetto.
     * @param string $imgData Dati binari dell'immagine.
     * @return array ['success' => bool]
     */
    public function addPhoto($projectName, $imgData) {
        try {
            // La procedura ora si aspetta: immagine, nome_progetto, in_email_creatore (NULL), OUT esito
            $stmt = $this->conn->prepare("CALL inserisci_foto(:immagine, :nome_progetto, NULL, @esito)");
            $stmt->bindParam(':immagine', $imgData, PDO::PARAM_LOB);
            $stmt->bindParam(':nome_progetto', $projectName);
            $result = $stmt->execute();
            // Recupero del parametro OUT
            $resultEsito = $this->conn->query("SELECT @esito as esito")->fetch(PDO::FETCH_ASSOC);
            if (!$resultEsito || !$resultEsito['esito']) {
                return ['success' => false];
            }
            $this->logger->log("Nuova foto aggiunta al progetto", [
                'nome_progetto' => $projectName
            ]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }
}
?>
