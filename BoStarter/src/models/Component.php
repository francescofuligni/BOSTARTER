<?php

/**
 * Classe per la gestione delle componenti hardware.
 * Fornisce metodi per recuperare e aggiungere componenti.
 */
class Component {
    private $conn;
    private $logger;

    public function __construct($db) {
        $this->conn = $db;
        $this->logger = new \MongoLogger();
    }

    /**
     * Recupera tutte le componenti hardware dal database (view componenti).
     *
     * @return array ['success' => bool, 'data' => array]
     */
    public function getAllComponents() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM componenti");
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * Recupera tutte le componenti di un progetto con quantità (view componenti_progetto).
     *
     * @param string $projectName
     * @return array ['success' => bool, 'data' => array]
     */
    public function getProjectComponents($projectName) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM componenti_progetto WHERE nome_progetto = :nome_progetto");
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->execute();
            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * Aggiunge una nuova componente hardware (solo creatori).
     *
     * @param string $name Nome della componente.
     * @param string $desc Descrizione della componente.
     * @param float $price Prezzo della componente.
     * @param string $creatorEmail Email del creatore.
     * @return array ['success' => bool]
     */
    public function addComponent($name, $desc, $price, $creatorEmail) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_componente(:nome, :descrizione, :prezzo, :email_creatore, @esito)");
            $stmt->bindParam(':nome', $name);
            $stmt->bindParam(':descrizione', $desc);
            $stmt->bindParam(':prezzo', $price);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $stmt->execute();
            $result = $this->conn->query("SELECT @esito as esito")->fetch(PDO::FETCH_ASSOC);
            if (!$result || !$result['esito']) {
                return ['success' => false];
            }
            $this->logger->log("Nuova componente hardware aggiunta", [
                'nome' => $name,
                'descrizione' => $desc,
                'prezzo' => $price,
                'email_creatore' => $creatorEmail
            ]);
            return ['success' => true];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Collega una componente a un progetto hardware (tabella COMPOSIZIONE).
     *
     * @param string $componentName Nome della componente.
     * @param int $quantity Quantità della componente.
     * @param string $projectName Nome del progetto.
     * @param string $creatorEmail Email del creatore.
     * @return array ['success' => bool]
     */
    public function addComponentToProject($componentName, $quantity, $projectName, $creatorEmail) {
        try {
            $stmt = $this->conn->prepare("CALL inserisci_composizione(:nome_componente, :quantita, :nome_progetto, :email_creatore, @is_creatore_progetto)");
            $stmt->bindParam(':nome_componente', $componentName);
            $stmt->bindParam(':quantita', $quantity);
            $stmt->bindParam(':nome_progetto', $projectName);
            $stmt->bindParam(':email_creatore', $creatorEmail);
            $stmt->execute();
            // Puoi anche controllare @is_creatore_progetto se vuoi
            return ['success' => true];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['success' => false];
        }
    }
}