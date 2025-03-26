USE bostarter_db;

-- TO CHECK


-- Procedura per l'autenticazione degli utenti normali
DELIMITER //
CREATE PROCEDURE authenticate_user(
    IN p_email VARCHAR(32),
    IN p_password VARCHAR(32),
    OUT p_authenticated BOOLEAN
)
BEGIN
    SELECT COUNT(*) > 0 INTO p_authenticated
    FROM UTENTE
    WHERE email = p_email AND password = p_password;
END //
DELIMITER ;


-- Procedura per l'autenticazione degli amministratori
DELIMITER //
CREATE PROCEDURE authenticate_admin(
    IN p_email VARCHAR(32),
    IN p_password VARCHAR(32),
    IN p_security_code CHAR(8),
    OUT p_authenticated BOOLEAN
)
BEGIN
    SELECT COUNT(*) > 0 INTO p_authenticated
    FROM UTENTE u
    JOIN UTENTE_AMMINISTRATORE ua ON u.email = ua.email_utente
    WHERE u.email = p_email 
    AND u.password = p_password 
    AND ua.codice_sicurezza = p_security_code;
END //
DELIMITER ;


-- Procedura per la registrazione di un nuovo utente
DELIMITER //
CREATE PROCEDURE register_user(
    IN p_email VARCHAR(32),
    IN p_password VARCHAR(32),
    IN p_nome VARCHAR(32),
    IN p_cognome VARCHAR(32),
    IN p_nickname VARCHAR(32),
    IN p_luogo_nascita VARCHAR(32),
    IN p_anno_nascita INT,
    IN p_is_creator BOOLEAN
)
BEGIN
    -- Inserisci l'utente base
    INSERT INTO UTENTE (email, password, nome, cognome, nickname, luogo_nascita, anno_nascita)
    VALUES (p_email, p_password, p_nome, p_cognome, p_nickname, p_luogo_nascita, p_anno_nascita);
    
    -- Se è un creatore, inseriscilo anche nella tabella UTENTE_CREATORE
    IF p_is_creator THEN
        INSERT INTO UTENTE_CREATORE (email_utente, affidabilita)
        VALUES (p_email, 0);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una skill nel curriculum
DELIMITER //
CREATE PROCEDURE add_skill(
    IN p_email VARCHAR(32),
    IN p_competenza VARCHAR(32),
    IN p_livello INT
)
BEGIN
    INSERT INTO SKILL_POSSEDUTA (email_utente, nome_competenza, livello)
    VALUES (p_email, p_competenza, p_livello);
END //
DELIMITER ;


-- Procedura per visualizzare i progetti disponibili
DELIMITER //
CREATE PROCEDURE get_available_projects()
BEGIN
    SELECT *
    FROM PROGETTO
    WHERE stato = 'APERTO';
END //
DELIMITER ;


-- Procedura per il finanziamento di un progetto
DELIMITER //
CREATE PROCEDURE fund_project(
    IN p_email_utente VARCHAR(32),
    IN p_nome_progetto VARCHAR(32),
    IN p_importo DECIMAL(16,2),
    IN p_codice_reward VARCHAR(32)
)
BEGIN
    INSERT INTO FINANZIAMENTO (data, nome_progetto, email_utente, importo, codice_reward)
    VALUES (CURDATE(), p_nome_progetto, p_email_utente, p_importo, p_codice_reward);
END //
DELIMITER ;


-- Procedura per l'inserimento di un commento
DELIMITER //
CREATE PROCEDURE add_comment(
    IN p_nome_progetto VARCHAR(32),
    IN p_email_utente VARCHAR(32),
    IN p_testo VARCHAR(255)
)
BEGIN
    INSERT INTO COMMENTO (nome_progetto, email_utente, testo, data)
    VALUES (p_nome_progetto, p_email_utente, p_testo, CURDATE());
END //
DELIMITER ;


-- Procedura per l'inserimento di una candidatura
DELIMITER //
CREATE PROCEDURE submit_application(
    IN p_email_utente VARCHAR(32),
    IN p_id_profilo INT
)
BEGIN
    INSERT INTO CANDIDATURA (email_utente, id_profilo, stato)
    VALUES (p_email_utente, p_id_profilo, 'IN ATTESA');
END //
DELIMITER ;


-- Procedura per l'inserimento di una nuova competenza (solo admin)
DELIMITER //
CREATE PROCEDURE add_competenza(
    IN p_nome VARCHAR(32),
    IN p_admin_email VARCHAR(32),
    IN p_admin_security_code CHAR(8)
)
BEGIN
    DECLARE is_admin BOOLEAN;
    
    -- Verifica che l'utente sia un amministratore
    SELECT COUNT(*) > 0 INTO is_admin
    FROM UTENTE_AMMINISTRATORE
    WHERE email_utente = p_admin_email
    AND codice_sicurezza = p_admin_security_code;
    
    IF is_admin THEN
        INSERT INTO COMPETENZA (nome)
        VALUES (p_nome);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di un nuovo progetto (solo creatori)
DELIMITER //
CREATE PROCEDURE create_project(
    IN p_nome VARCHAR(32),
    IN p_descrizione VARCHAR(255),
    IN p_budget DECIMAL(16,2),
    IN p_data_limite DATE,
    IN p_tipo ENUM ('SOFTWARE', 'HARDWARE'),
    IN p_email_creatore VARCHAR(32)
)
BEGIN
    DECLARE is_creator BOOLEAN;
    
    -- Verifica che l'utente sia un creatore
    SELECT COUNT(*) > 0 INTO is_creator
    FROM UTENTE_CREATORE
    WHERE email_utente = p_email_creatore;
    
    IF is_creator THEN
        INSERT INTO PROGETTO (nome, descrizione, budget, data_inserimento, data_limite, stato, tipo, email_utente_creatore)
        VALUES (p_nome, p_descrizione, p_budget, CURDATE(), p_data_limite, 'APERTO', p_tipo, p_email_creatore);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una reward (solo creatori)
DELIMITER //
CREATE PROCEDURE add_reward(
    IN p_codice VARCHAR(32),
    IN p_immagine BLOB,
    IN p_descrizione VARCHAR(255),
    IN p_nome_progetto VARCHAR(32),
    IN p_email_creatore VARCHAR(32)
)
BEGIN
    DECLARE is_project_owner BOOLEAN;
    
    -- Verifica che l'utente sia il creatore del progetto
    SELECT COUNT(*) > 0 INTO is_project_owner
    FROM PROGETTO
    WHERE nome = p_nome_progetto
    AND email_utente_creatore = p_email_creatore;
    
    IF is_project_owner THEN
        INSERT INTO REWARD (codice, immagine, descrizione, nome_progetto)
        VALUES (p_codice, p_immagine, p_descrizione, p_nome_progetto);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una risposta ad un commento (solo creatori)
DELIMITER //
CREATE PROCEDURE add_comment_response(
    IN p_id_commento INT,
    IN p_testo VARCHAR(255),
    IN p_email_creatore VARCHAR(32)
)
BEGIN
    DECLARE is_project_owner BOOLEAN;
    
    -- Verifica che l'utente sia il creatore del progetto associato al commento
    SELECT COUNT(*) > 0 INTO is_project_owner
    FROM COMMENTO c
    JOIN PROGETTO p ON c.nome_progetto = p.nome
    WHERE c.id = p_id_commento
    AND p.email_utente_creatore = p_email_creatore;
    
    IF is_project_owner THEN
        INSERT INTO RISPOSTA (id_commento, testo)
        VALUES (p_id_commento, p_testo);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di un profilo (solo creatori, solo progetti software)
DELIMITER //
CREATE PROCEDURE add_profile(
    IN p_nome VARCHAR(32),
    IN p_nome_progetto VARCHAR(32),
    IN p_email_creatore VARCHAR(32)
)
BEGIN
    DECLARE is_software_project_owner BOOLEAN;
    
    -- Verifica che l'utente sia il creatore del progetto e che sia un progetto software
    SELECT COUNT(*) > 0 INTO is_software_project_owner
    FROM PROGETTO
    WHERE nome = p_nome_progetto
    AND email_utente_creatore = p_email_creatore
    AND tipo = 'SOFTWARE';
    
    IF is_software_project_owner THEN
        INSERT INTO PROFILO (nome, nome_progetto)
        VALUES (p_nome, p_nome_progetto);
    END IF;
END //
DELIMITER ;


-- Procedura per gestire una candidatura (solo creatori)
DELIMITER //
CREATE PROCEDURE manage_application(
    IN p_email_candidato VARCHAR(32),
    IN p_id_profilo INT,
    IN p_email_creatore VARCHAR(32),
    IN p_nuovo_stato ENUM ('ACCETTATA', 'RIFIUTATA')
)
BEGIN
    DECLARE is_project_owner BOOLEAN;
    
    -- Verifica che l'utente sia il creatore del progetto associato al profilo
    SELECT COUNT(*) > 0 INTO is_project_owner
    FROM PROFILO pr
    JOIN PROGETTO p ON pr.nome_progetto = p.nome
    WHERE pr.id = p_id_profilo
    AND p.email_utente_creatore = p_email_creatore;
    
    IF is_project_owner THEN
        UPDATE CANDIDATURA
        SET stato = p_nuovo_stato
        WHERE email_utente = p_email_candidato
        AND id_profilo = p_id_profilo;
    END IF;
END //
DELIMITER ;
