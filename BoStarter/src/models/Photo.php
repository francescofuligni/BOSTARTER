<?php
class Photo {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function addPhotoToProject($nome_progetto, $imgData) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO FOTO (nome_progetto, immagine) VALUES (:nome_progetto, :immagine)");
            $stmt->bindParam(':nome_progetto', $nome_progetto);
            $stmt->bindParam(':immagine', $imgData, PDO::PARAM_LOB); // <-- fondamentale!
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Errore DB: " . $e->getMessage());
            return false;
        }
    }
}
?>