USE bostarter_db;


-- TUTTI GLI UTENTI ------------------------------------------------------------


-- Procedura per l'autenticazione di un utente
DELIMITER //
CREATE PROCEDURE autenticazione_utente (
    IN in_email VARCHAR(32),
    IN in_password VARCHAR(32),
    OUT autenticato BOOLEAN
)
BEGIN
    SELECT COUNT(*) > 0 INTO autenticato
    FROM UTENTE
    WHERE email = in_email AND password = in_password;
END //
DELIMITER ;


-- Procedura per la registrazione di un nuovo utente
-- TODO (?): registrazione come amministratore --> restituisce in output codice_sicurezza
DELIMITER //
CREATE PROCEDURE registrazione_utente (
    IN new_email VARCHAR(32),
    IN new_password VARCHAR(32),
    IN new_nome VARCHAR(32),
    IN new_cognome VARCHAR(32),
    IN new_nickname VARCHAR(32),
    IN new_luogo_nascita VARCHAR(32),
    IN new_anno_nascita INT,
    IN is_creatore BOOLEAN
)
BEGIN
    INSERT INTO UTENTE (email, password, nome, cognome, nickname, luogo_nascita, anno_nascita)
    VALUES (in_email, in_password, in_nome, in_cognome, in_nickname, in_luogo_nascita, in_anno_nascita);

    IF is_creatore THEN
        INSERT INTO UTENTE_CREATORE (email_utente)
        VALUES (in_email);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una skill nel curriculum
DELIMITER //
CREATE PROCEDURE aggiungi_skill(
    IN in_email VARCHAR(32),
    IN in_competenza VARCHAR(32),
    IN in_livello INT
)
BEGIN
    INSERT INTO SKILL_POSSEDUTA (email_utente, nome_competenza, livello)
    VALUES (in_email, in_competenza, in_livello);
END //
DELIMITER ;


-- Procedura per visualizzare i progetti disponibili
DELIMITER //
CREATE PROCEDURE mostra_progetti_aperti()
BEGIN
    SELECT *
    FROM PROGETTO
    WHERE stato = 'APERTO';
END //
DELIMITER ;


-- Procedura per il finanziamento di un progetto con insertimento reward
DELIMITER //
CREATE PROCEDURE finanzia_progetto(
    IN in_email_utente VARCHAR(32),
    IN in_nome_progetto VARCHAR(32),
    IN in_importo DECIMAL(16,2),
    IN in_codice_reward VARCHAR(32),
)
BEGIN
    DECLARE is_aperto BOOLEAN;
    SET is_aperto = (SELECT stato FROM PROGETTO WHERE nome = in_nome_progetto) = 'APERTO';
    
    IF is_aperto THEN
        INSERT INTO FINANZIAMENTO (data, nome_progetto, email_utente, importo, codice_reward)
        VALUES (CURDATE(), in_nome_progetto, in_email_utente, in_importo, in_codice_reward);
    END IF;
END //
DELIMITER ;


-- Procedura per la scelta di una reward
-- TODO: da pensare bene


-- Procedura per l'inserimento di un commento
DELIMITER //
CREATE PROCEDURE inserisci_commento(
    IN in_nome_progetto VARCHAR(32),
    IN in_email_utente VARCHAR(32),
    IN in_testo VARCHAR(255),
)
BEGIN
    INSERT INTO COMMENTO (nome_progetto, email_utente, testo, data)
    VALUES (in_nome_progetto, in_email_utente, in_testo, CURDATE());
END //
DELIMITER ;


-- Procedura per l'inserimento di una candidatura
DELIMITER //
CREATE PROCEDURE inserisci_candidatura(
    IN in_email_utente VARCHAR(32),
    IN in_id_profilo INT
)
BEGIN
    INSERT INTO CANDIDATURA (email_utente, id_profilo, stato)
    VALUES (in_email_utente, in_id_profilo, 'IN ATTESA');
END //
DELIMITER ;




-- SOLO AMMINISTRATORI ------------------------------------------------------------


-- Procedura per l'autenticazione (solo amministratori)
DELIMITER //
CREATE PROCEDURE autenticazione_amministratore(
    IN in_email VARCHAR(32),
    IN in_password VARCHAR(32),
    IN in_codice_sicurezza CHAR(8),
    OUT autenticato BOOLEAN
)
BEGIN
    SELECT COUNT(*) > 0 INTO autenticato
    FROM UTENTE u
    JOIN UTENTE_AMMINISTRATORE ua ON u.email = ua.email_utente
    WHERE u.email = in_email 
    AND u.password = in_password 
    AND ua.codice_sicurezza = in_codice_sicurezza;
END //
DELIMITER ;


-- Procedura per l'inserimento di una nuova competenza (solo amministratori)
DELIMITER //
CREATE PROCEDURE aggiungi_competenza(
    IN in_competenza VARCHAR(32),
    IN in_admin_email VARCHAR(32),
    IN in_admin_security_code CHAR(8)
)
BEGIN
    DECLARE is_amministratore BOOLEAN;
    
    SELECT COUNT(*) > 0 INTO is_amministratore
    FROM UTENTE_AMMINISTRATORE
    WHERE email_utente = in_admin_email
    AND codice_sicurezza = in_admin_security_code;
    
    IF is_amministratore THEN
        INSERT INTO COMPETENZA (nome)
        VALUES (in_competenza);
    END IF;
END //
DELIMITER ;




-- SOLO CREATORI ------------------------------------------------------------


-- Procedura per l'inserimento di un nuovo progetto (solo creatori)
DELIMITER //
CREATE PROCEDURE crea_progetto(
    IN in_nome VARCHAR(32),
    IN in_descrizione VARCHAR(255),
    IN in_budget DECIMAL(16,2),
    IN in_data_limite DATE,
    IN in_tipo ENUM ('SOFTWARE', 'HARDWARE'),
    IN in_email_creatore VARCHAR(32)
)
BEGIN
    DECLARE is_creatore BOOLEAN;
    
    SELECT COUNT(*) > 0 INTO is_creatore
    FROM UTENTE_CREATORE
    WHERE email_utente = in_email_creatore;
    
    IF is_creatore AND data_limite > CURDATE() THEN
        INSERT INTO PROGETTO (nome, descrizione, budget, data_inserimento, data_limite, stato, tipo, email_utente_creatore)
        VALUES (in_nome, in_descrizione, in_budget, CURDATE(), in_data_limite, 'APERTO', in_tipo, in_email_creatore);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una reward (solo creatori)
DELIMITER //
CREATE PROCEDURE inserisci_reward(
    IN in_codice VARCHAR(32),
    IN in_immagine BLOB,
    IN in_descrizione VARCHAR(255),
    IN in_nome_progetto VARCHAR(32),
    IN in_email_creatore VARCHAR(32)
)
BEGIN
    DECLARE is_creatore_progetto BOOLEAN;
    
    -- Verifica che l'utente sia il creatore del progetto
    SELECT COUNT(*) > 0 INTO is_creatore_progetto
    FROM PROGETTO
    WHERE nome = in_nome_progetto
    AND email_utente_creatore = in_email_creatore;
    
    IF is_creatore_progetto THEN
        INSERT INTO REWARD (codice, immagine, descrizione, nome_progetto)
        VALUES (in_codice, in_immagine, in_descrizione, in_nome_progetto);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una risposta ad un commento (solo creatori)
DELIMITER //
CREATE PROCEDURE inserisci_risposta(
    IN in_id_commento INT,
    IN in_testo VARCHAR(255),
    IN in_email_creatore VARCHAR(32)
)
BEGIN
    DECLARE is_creatore_progetto BOOLEAN;
    
    -- Verifica che l'utente sia il creatore del progetto associato al commento
    SELECT COUNT(*) > 0 INTO is_creatore_progetto
    FROM COMMENTO c
    JOIN PROGETTO p ON c.nome_progetto = p.nome
    WHERE c.id = in_id_commento
    AND p.email_utente_creatore = in_email_creatore;
    
    IF is_creatore_progetto THEN
        INSERT INTO RISPOSTA (id_commento, testo)
        VALUES (in_id_commento, in_testo);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di un profilo su un progetto software (solo creatori)
DELIMITER //
CREATE PROCEDURE inserisci_profilo(
    IN in_nome VARCHAR(32),
    IN in_nome_progetto VARCHAR(32),
    IN in_email_creatore VARCHAR(32)
)
BEGIN
    DECLARE is_creatore_progetto_software BOOLEAN;
    
    SELECT COUNT(*) > 0 INTO is_creatore_progetto_software
    FROM PROGETTO
    WHERE nome = in_nome_progetto
    AND email_utente_creatore = in_email_creatore
    AND tipo = 'SOFTWARE';
    
    IF is_creatore_progetto_software THEN
        INSERT INTO PROFILO (nome, nome_progetto)
        VALUES (in_nome, in_nome_progetto);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una skill richiesta per un profilo (solo creatori)
-- TO CHECK: OPERAZIONE NON RICHIESTA NEL FILE MA NECESSARIA PER COME È STRUTTURATO IL PROGETTO
DELIMITER //
CREATE PROCEDURE inserisci_skill_richiesta(
    IN in_id_profilo VARCHAR(32),
    IN in_email_creatore VARCHAR(32),
    IN in_competenza VARCHAR(32),
    IN in_livello INT
)
BEGIN
    DECLARE is_creatore_progetto BOOLEAN;

    SELECT COUNT(*) > 0 INTO is_creatore_progetto
    FROM PROFILO pr
    JOIN PROGETTO p ON pr.nome_progetto = p.nome
    WHERE pr.id = in_id_profilo
    AND p.email_utente_creatore = in_email_creatore;
    
    IF is_creatore_progetto THEN
        INSERT INTO SKILL_RICHIESTA (id_profilo, nome_competenza, livello)
        VALUES (in_id_profilo, in_competenza, in_livello);
    END IF;
END //
DELIMITER ;


-- Procedura per gestire una candidatura (solo creatori)
DELIMITER //
CREATE PROCEDURE gestisci_candidatura(
    IN in_email_candidato VARCHAR(32),
    IN in_id_profilo INT,
    IN in_email_creatore VARCHAR(32),
    IN in_stato ENUM ('ACCETTATA', 'RIFIUTATA')
)
BEGIN
    DECLARE is_creatore_progetto BOOLEAN;
    
    SELECT COUNT(*) > 0 INTO is_creatore_progetto
    FROM PROFILO pr
    JOIN PROGETTO p ON pr.nome_progetto = p.nome
    WHERE pr.id = in_id_profilo
    AND p.email_utente_creatore = in_email_creatore;
    
    IF is_creatore_progetto THEN
        UPDATE CANDIDATURA
        SET stato = in_stato
        WHERE email_utente = in_email_candidato
        AND id_profilo = in_id_profilo;
    END IF;
END //
DELIMITER ;
