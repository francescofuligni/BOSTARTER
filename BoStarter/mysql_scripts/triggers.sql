USE bostarter_db;


-- Trigger per aggiornare l'affidabilità quando viene creato un nuovo progetto
DROP TRIGGER IF EXISTS aggiorna_affidabilita_nuovo_progetto;

DELIMITER //
CREATE TRIGGER aggiorna_affidabilita_nuovo_progetto
AFTER INSERT ON PROGETTO
FOR EACH ROW
BEGIN
    DECLARE progetti_totali INT DEFAULT 0;
    DECLARE progetti_finanziati INT DEFAULT 0;
    
    -- Conta il numero totale di progetti dell'utente
    SELECT COUNT(*) INTO progetti_totali
    FROM PROGETTO
    WHERE email_utente_creatore = NEW.email_utente_creatore;

    -- Conta il numero di progetti finanziati almeno una volta
    SELECT COUNT(DISTINCT p.nome) INTO progetti_finanziati
    FROM PROGETTO p
    JOIN FINANZIAMENTO f ON p.nome = f.nome_progetto
    WHERE p.email_utente_creatore = NEW.email_utente_creatore;
    
    -- Aggiorna l'affidabilità
    IF progetti_totali > 0 THEN
        UPDATE UTENTE_CREATORE
        SET affidabilita = (progetti_finanziati * 100 / progetti_totali)
        WHERE email_utente = NEW.email_utente_creatore;
    END IF;
END //
DELIMITER ;


-- Trigger per aggiornare l'affidabilità quando viene aggiunto un finanziamento
DROP TRIGGER IF EXISTS aggiorna_affidabilita_nuovo_finanziamento;

DELIMITER //
CREATE TRIGGER aggiorna_affidabilita_nuovo_finanziamento
AFTER INSERT ON FINANZIAMENTO
FOR EACH ROW
BEGIN
    DECLARE progetti_totali INT DEFAULT 0;
    DECLARE progetti_finanziati INT DEFAULT 0;
    DECLARE email VARCHAR(32) DEFAULT '';

    -- Ottiene l'email dell'utente creatore
    SELECT email_utente_creatore INTO email
    FROM PROGETTO
    WHERE nome = NEW.nome_progetto;
    
    -- Conta il numero totale di progetti dell'utente
    SELECT COUNT(*) INTO progetti_totali
    FROM PROGETTO
    WHERE email_utente_creatore = email;

    -- Conta il numero di progetti finanziati almeno una volta
    SELECT COUNT(DISTINCT p.nome) INTO progetti_finanziati
    FROM PROGETTO p
    JOIN FINANZIAMENTO f ON p.nome = f.nome_progetto
    WHERE p.email_utente_creatore = email;

    -- Aggiorna l'affidabilità
    IF progetti_totali > 0 THEN
        UPDATE UTENTE_CREATORE
        SET affidabilita = (progetti_finanziati * 100 / progetti_totali)
        WHERE email_utente = email;
    END IF;
END //
DELIMITER ;


-- Trigger per cambiare lo stato di un progetto al raggiungimento del budget
DROP TRIGGER IF EXISTS aggiorna_stato_progetto;

DELIMITER //
CREATE TRIGGER aggiorna_stato_progetto
AFTER INSERT ON FINANZIAMENTO
FOR EACH ROW
BEGIN
    DECLARE budget_progetto DECIMAL(16,2);
    DECLARE totale_finanziamenti DECIMAL(16,2);
    
    SELECT budget INTO budget_progetto
    FROM PROGETTO
    WHERE nome = NEW.nome_progetto;
    
    SELECT SUM(importo) INTO totale_finanziamenti
    FROM FINANZIAMENTO
    WHERE nome_progetto = NEW.nome_progetto;
    
    IF totale_finanziamenti >= budget_progetto THEN
        UPDATE PROGETTO
        SET stato = 'CHIUSO'
        WHERE nome = NEW.nome_progetto;
    END IF;
END //
DELIMITER ;


-- Trigger per incrementare il numero di progetti di un utente creatore
-- RIDONDANZA CONCETTUALE RIMOSSA
/*
DROP TRIGGER IF EXISTS incrementa_nr_progetti;

DELIMITER //
CREATE TRIGGER incrementa_nr_progetti
AFTER INSERT ON PROGETTO
FOR EACH ROW
BEGIN
    UPDATE UTENTE_CREATORE
    SET nr_progetti = nr_progetti + 1
    WHERE email_utente = NEW.email_utente_creatore;
END //
DELIMITER ;
*/


-- Trigger per aggiornare somma_raccolta quando viene inserito un finanziamento --
DROP TRIGGER IF EXISTS aggiorna_somma_raccolta;

DELIMITER //
CREATE TRIGGER aggiorna_somma_raccolta
AFTER INSERT ON FINANZIAMENTO
FOR EACH ROW
BEGIN
    UPDATE PROGETTO
    SET somma_raccolta = somma_raccolta + NEW.importo
    WHERE nome = NEW.nome_progetto;
END //
DELIMITER ;


-- Trigger per rifiutare le altre candidature all'accettazione di una candidatura --
DROP TRIGGER IF EXISTS rifiuta_candidature;

DELIMITER //
CREATE TRIGGER rifiuta_candidature
AFTER UPDATE ON PROFILO
FOR EACH ROW
BEGIN
    IF NEW.stato = 'OCCUPATO' THEN
        UPDATE CANDIDATURA
        SET stato = 'RIFIUTATA'
        WHERE id_profilo = NEW.id AND stato = 'ATTESA';
    END IF;
END //
