<?php
class Project {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ottieni tutti i progetti attivi tramite la view
    public function getOpenProjects() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM progetti_aperti");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Ottieni tutte le foto di un progetto tramite query
    public function getProjectPhotos($nomeProgetto) {
        try {
            $stmt = $this->conn->prepare("SELECT immagine FROM foto_progetto WHERE nome_progetto = :nome_progetto");
            $stmt->bindParam(':nome_progetto', $nomeProgetto);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Ottieni tutti i commenti di un progetto tramite query
    public function getProjectComments($nomeProgetto) {
        try {
            $stmt = $this->conn->prepare("SELECT testo, nickname, data FROM commenti_progetto WHERE nome_progetto = :nome_progetto ORDER BY data DESC");
            $stmt->bindParam(':nome_progetto', $nomeProgetto);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Ottieni tutti i progetti creati da un utente tramite query
    public function getProjectsByCreator($email) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM PROGETTO WHERE creatore = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Aggiungi un nuovo progetto
    public function createProject($nome, $descrizione, $budget, $data_limite, $tipo, $email_creatore, $foto) {
        // foto è un'array di longblob --> iterare sulle foto richiamando la stored procedure inserisci_foto
    }
}
?>