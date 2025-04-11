USE bostarter_db;

SET GLOBAL event_scheduler = ON;

-- Evento per chiudere i progetti scaduti (eseguito ogni giorno)
DROP EVENT IF EXISTS chiudi_progetti_scaduti;

--- testare con every 1 minute
DELIMITER //
CREATE EVENT chiudi_progetti_scaduti
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