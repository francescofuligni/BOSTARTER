<?php

class Project {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Ottieni tutti i progetti aperti dalla vista dedicata
     * @return array
     */
    public function getOpenProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_aperti");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ottieni tutte le immagini associate a un progetto
     * @param string $projectName
     * @return array
     */
    public function getProjectPhotos($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT immagine FROM foto_progetto WHERE nome_progetto = :nome_progetto");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ottieni tutti i commenti relativi a un progetto
     * @param string $projectName
     * @return array
     */
    public function getProjectComments($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT id, testo, nickname, data, risposta FROM commenti_progetto WHERE nome_progetto = :nome_progetto ORDER BY data DESC");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ottieni i dettagli di un progetto dalla vista progetti_con_foto
     * @param string $projectName
     * @return array|null
     */
    private function getProjectDetail($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto WHERE nome = :nome");
            $stmt->bindParam(':nome', $projectName);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Verifica se l'utente ha già finanziato il progetto nella data odierna
     * @param string $projectName
     * @param string $userEmail
     * @return bool
     */
    public function hasFundedToday($projectName, $userEmail) {
        try {
            $stmt = $this->conn->prepare(   // FORSE MEGLIO FATTO CON UNA STORED PROCEDURE?
                "SELECT COUNT(*) FROM FINANZIAMENTO 
                 WHERE nome_progetto = :nome_progetto 
                 AND email_utente = :email_utente 
                 AND data = CURDATE()"
            );
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Ottieni tutti i progetti con la prima foto associata
     * @return array
     */
    public function getAllProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Ottieni tutti i progetti creati da un utente
     * @param string $userEmail
     * @return array
     */
    public function getUserProjects($userEmail) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_con_foto WHERE email_utente_creatore = :email_utente");
            $stmt->bindParam(':email_utente', $userEmail);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ottieni tutte le ricompense associate a un progetto
     * @param string $projectName
     * @return array
     */
    public function getProjectRewards($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT codice, descrizione, immagine FROM REWARD WHERE nome_progetto = :nome");
            $stmt->bindParam(':nome', $projectName);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Gestisce il finanziamento di un progetto
     * @param Database $db
     */
    public function fundProject($db) {
        $projectName = $_POST['nome_progetto'] ?? '';
        $amount = floatval($_POST['importo'] ?? 0);
        $userEmail = $_SESSION['user_id'] ?? '';
        $rewardCode = $_POST['codice_reward'] ?? '';

        // Get the PDO connection from the Database object
        $pdo = $db instanceof Database ? $db->getConnection() : $db;

        if ($projectName && $amount > 0 && $userEmail && $rewardCode) {
            try {
                $stmt = $pdo->prepare("CALL finanzia_progetto(:email_utente, :nome_progetto, :importo)");
                $stmt->bindParam(':email_utente', $userEmail);
                $stmt->bindParam(':nome_progetto', $projectName);
                $stmt->bindParam(':importo', $amount);
                if ($stmt->execute()) {
                    // Associa la reward al finanziamento appena inserito
                    $stmt2 = $pdo->prepare("CALL scegli_reward(:email_utente, :nome_progetto, :codice_reward)");
                    $stmt2->bindParam(':email_utente', $userEmail);
                    $stmt2->bindParam(':nome_progetto', $projectName);
                    $stmt2->bindParam(':codice_reward', $rewardCode);
                    $stmt2->execute();
                    $_SESSION['success'] = "Finanziamento effettuato con successo!";
                } else {
                    $_SESSION['error'] = "Errore nell'inserimento del finanziamento.";
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Errore nell'inserimento del finanziamento.";
            }
        } else {
            $_SESSION['error'] = "Compila tutti i campi per finanziare il progetto.";
        }
        header('Location: /project-detail?nome=' . urlencode($projectName));
        exit;
    }

    /**
     * Gestisce l'inserimento di una risposta
     * @param User $user
     */
    public function addReply($user) {
        $commentId = $_POST['id_commento'] ?? '';
        $responseText = trim($_POST['testo_risposta'] ?? '');
        $creatorEmail = $_SESSION['user_id'] ?? '';
        $projectName = $_POST['nome_progetto'] ?? '';

        if ($commentId && $responseText && $creatorEmail) {
            if ($user->addReply($commentId, $responseText, $creatorEmail)) {
                $_SESSION['success'] = "Risposta aggiunta con successo!";
            } else {
                $_SESSION['error'] = "Errore nell'inserimento della risposta.";
            }
        } else {
            $_SESSION['error'] = "Compila tutti i campi per inserire una risposta.";
        }
        header('Location: /project-detail?nome=' . urlencode($projectName));
        exit;
    }

    /**
     * Recupera i dati del progetto, le foto e i commenti
     * @param Project $projectModel
     * @param string $projectName
     * @return array
     */
    public function getProjectDetailData($projectModel, $projectName) {
        $project = $projectModel->getProjectDetail($projectName);
        $photos = $projectModel->getProjectPhotos($projectName);
        $comments = $projectModel->getProjectComments($projectName);
        return [$project, $photos, $comments];
    }

    /**
     * Gestisce l'inserimento di un commento
     * @param User $user
     */
    public function addComment($user) {
        $projectName = $_POST['nome_progetto'] ?? '';
        $commentText = trim($_POST['testo_commento'] ?? '');
        $userEmail = $_SESSION['user_id'] ?? '';

        if ($projectName && $commentText && $userEmail) {
            if ($user->addComment($projectName, $userEmail, $commentText)) {
                $_SESSION['success'] = "Commento aggiunto con successo!";
            } else {
                $_SESSION['error'] = "Errore nell'inserimento del commento.";
            }
        } else {
            $_SESSION['error'] = "Compila tutti i campi per inserire un commento.";
        }
        header('Location: /project-detail?nome=' . urlencode($projectName));
        exit;
    }
}
?>
