USE bostarter_db;

-- Abilita gli eventi
SET GLOBAL event_scheduler = ON;

-- Evento per chiudere i progetti scaduti (eseguito ogni giorno)
DELIMITER //
CREATE EVENT close_expired_projects
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    UPDATE PROGETTO
    SET stato = 'CHIUSO'
    WHERE stato = 'APERTO'
    AND data_limite < CURDATE();
END //
DELIMITER ;