USE bostarter_db;


-- Trigger per aggiornare l'affidabilità quando viene creato un nuovo progetto
-- TO CHECK
DELIMITER //
CREATE TRIGGER update_affidabilita_new_project
AFTER INSERT ON PROGETTO
FOR EACH ROW
BEGIN
    DECLARE progetti_totali INT;
    DECLARE progetti_finanziati INT;
    
    -- Conta il numero totale di progetti dell'utente
    SELECT COUNT(*) INTO progetti_totali
    FROM PROGETTO
    WHERE email_utente_creatore = NEW.email_utente_creatore;
    
    -- Conta il numero di progetti che hanno raggiunto il budget
    SELECT COUNT(DISTINCT p.nome) INTO progetti_finanziati
    FROM PROGETTO p
    LEFT JOIN (
        SELECT nome_progetto, SUM(importo) as totale_finanziamenti
        FROM FINANZIAMENTO
        GROUP BY nome_progetto
    ) f ON p.nome = f.nome_progetto
    WHERE p.email_utente_creatore = NEW.email_utente_creatore
    AND COALESCE(f.totale_finanziamenti, 0) >= p.budget;
    
    -- Aggiorna l'affidabilità
    IF progetti_totali > 0 THEN
        UPDATE UTENTE_CREATORE
        SET affidabilita = (progetti_finanziati * 100 / progetti_totali)
        WHERE email_utente = NEW.email_utente_creatore;
    END IF;
END //
DELIMITER ;


-- Trigger per aggiornare l'affidabilità quando viene aggiunto un finanziamento
-- TO CHECK
DELIMITER //
CREATE TRIGGER update_affidabilita_new_funding
AFTER INSERT ON FINANZIAMENTO
FOR EACH ROW
BEGIN
    DECLARE creator_email VARCHAR(32);
    DECLARE progetti_totali INT;
    DECLARE progetti_finanziati INT;
    DECLARE budget_progetto DECIMAL(16,2);
    DECLARE totale_finanziamenti DECIMAL(16,2);
    
    -- Ottieni l'email del creatore del progetto
    SELECT email_utente_creatore, budget INTO creator_email, budget_progetto
    FROM PROGETTO
    WHERE nome = NEW.nome_progetto;
    
    -- Calcola il totale dei finanziamenti per questo progetto
    SELECT SUM(importo) INTO totale_finanziamenti
    FROM FINANZIAMENTO
    WHERE nome_progetto = NEW.nome_progetto;
    
    -- Se il progetto ha raggiunto il budget, aggiorna l'affidabilità
    IF totale_finanziamenti >= budget_progetto THEN
        -- Conta il numero totale di progetti dell'utente
        SELECT COUNT(*) INTO progetti_totali
        FROM PROGETTO
        WHERE email_utente_creatore = creator_email;
        
        -- Conta il numero di progetti che hanno raggiunto il budget
        SELECT COUNT(DISTINCT p.nome) INTO progetti_finanziati
        FROM PROGETTO p
        LEFT JOIN (
            SELECT nome_progetto, SUM(importo) as totale_finanziamenti
            FROM FINANZIAMENTO
            GROUP BY nome_progetto
        ) f ON p.nome = f.nome_progetto
        WHERE p.email_utente_creatore = creator_email
        AND COALESCE(f.totale_finanziamenti, 0) >= p.budget;
        
        -- Aggiorna l'affidabilità
        IF progetti_totali > 0 THEN
            UPDATE UTENTE_CREATORE
            SET affidabilita = (progetti_finanziati * 100 / progetti_totali)
            WHERE email_utente = creator_email;
        END IF;
    END IF;
END //
DELIMITER ;


-- Trigger per cambiare lo stato di un progetto al raggiungimento del budget
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
