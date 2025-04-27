USE bostarter_db;





-- PROCEDURE DI CONTROLLO --------------------------------------------

-- Procedura per verificare se un utente è creatore
DROP PROCEDURE IF EXISTS verifica_creatore;

DELIMITER //
CREATE PROCEDURE verifica_creatore(
    IN in_email VARCHAR(32),
    OUT esito BOOLEAN
)
BEGIN
    SELECT COUNT(*) > 0 INTO esito
    FROM UTENTE_CREATORE
    WHERE email_utente = in_email;
END //
DELIMITER ;


-- Procedura per verificare se un utente è amministratore
DROP PROCEDURE IF EXISTS verifica_amministratore;

DELIMITER //
CREATE PROCEDURE verifica_amministratore(
    IN in_email VARCHAR(32),
    IN in_codice_sicurezza CHAR(64),
    OUT esito BOOLEAN
)
BEGIN
    SELECT COUNT(*) > 0 INTO esito
    FROM UTENTE_AMMINISTRATORE
    WHERE email_utente = in_email
    AND codice_sicurezza = in_codice_sicurezza;
END //
DELIMITER ;


-- Procedura per verificare se un progetto è aperto
DROP PROCEDURE IF EXISTS verifica_progetto_aperto;

DELIMITER //
CREATE PROCEDURE verifica_progetto_aperto(
    IN in_nome_progetto VARCHAR(32),
    OUT esito BOOLEAN
)
BEGIN
    SELECT COUNT(*) > 0 INTO esito
    FROM PROGETTO
    WHERE nome = in_nome_progetto
    AND stato = 'APERTO';
END //
DELIMITER ;


-- Procedura per verificare se un utente è creatore di un progetto specifico
DROP PROCEDURE IF EXISTS verifica_creatore_progetto;

DELIMITER //
CREATE PROCEDURE verifica_creatore_progetto(
    IN in_nome_progetto VARCHAR(32),
    IN in_email_creatore VARCHAR(32),
    OUT esito BOOLEAN
)
BEGIN
    SELECT COUNT(*) > 0 INTO esito
    FROM PROGETTO
    WHERE nome = in_nome_progetto
    AND email_utente_creatore = in_email_creatore;
END //
DELIMITER ;


-- Procedura per verificare la tipologia di un progetto
DROP PROCEDURE IF EXISTS verifica_tipo_progetto;

DELIMITER //
CREATE PROCEDURE verifica_tipo_progetto(
    IN in_nome_progetto VARCHAR(32),
    IN in_tipo ENUM('SOFTWARE', 'HARDWARE'),
    OUT esito BOOLEAN
)
BEGIN
    SELECT COUNT(*) > 0 INTO esito
    FROM PROGETTO
    WHERE nome = in_nome_progetto
    AND tipo = in_tipo;
END //
DELIMITER ;





-- TUTTI -----------------------------------------------------------------------


-- Procedura per l'autenticazione di un utente (utente normale o creatore)
DROP PROCEDURE IF EXISTS autenticazione_utente;

DELIMITER //
CREATE PROCEDURE autenticazione_utente (
    IN in_email VARCHAR(32),
    IN in_password CHAR(64),
    OUT esito BOOLEAN
)
BEGIN
    SELECT COUNT(*) > 0 INTO esito
    FROM UTENTE
    WHERE email = in_email AND password = in_password;
END //
DELIMITER ;


-- Procedura per la registrazione di un nuovo utente
DROP PROCEDURE IF EXISTS registrazione_utente;

DELIMITER //
CREATE PROCEDURE registrazione_utente (
    IN in_email VARCHAR(32),
    IN in_password CHAR(64),
    IN in_nome VARCHAR(32),
    IN in_cognome VARCHAR(32),
    IN in_nickname VARCHAR(32),
    IN in_luogo_nascita VARCHAR(32),
    IN in_anno_nascita INT,
    IN tipo ENUM ('UTENTE', 'CREATORE', 'AMMINISTRATORE'),
    IN in_codice_sicurezza CHAR(64)
)
BEGIN
    INSERT INTO UTENTE (email, password, nome, cognome, nickname, luogo_nascita, anno_nascita)
    VALUES (in_email, in_password, in_nome, in_cognome, in_nickname, in_luogo_nascita, in_anno_nascita);

    IF tipo = 'CREATORE' THEN
        INSERT INTO UTENTE_CREATORE (email_utente)
        VALUES (in_email);
    ELSEIF tipo = 'AMMINISTRATORE' THEN
        INSERT INTO UTENTE_AMMINISTRATORE (email_utente, codice_sicurezza)
        VALUES (in_email, in_codice_sicurezza);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una skill nel curriculum
DROP PROCEDURE IF EXISTS aggiungi_skill;

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


-- Procedura per il finanziamento di un progetto
DROP PROCEDURE IF EXISTS finanzia_progetto;

DELIMITER //
CREATE PROCEDURE finanzia_progetto(
    IN in_email_utente VARCHAR(32),
    IN in_nome_progetto VARCHAR(32),
    IN in_importo DECIMAL(16,2),
    OUT is_progetto_aperto BOOLEAN
)
BEGIN
    CALL verifica_progetto_aperto(in_nome_progetto, is_progetto_aperto);

    IF is_progetto_aperto THEN
        INSERT INTO FINANZIAMENTO (data, nome_progetto, email_utente, importo)
        VALUES (CURDATE(), in_nome_progetto, in_email_utente, in_importo);
    END IF;
END //
DELIMITER ;


-- Procedura per la scelta della reward
DROP PROCEDURE IF EXISTS scegli_reward;

DELIMITER //
CREATE PROCEDURE scegli_reward(
    IN in_email_utente VARCHAR(32),
    IN in_nome_progetto VARCHAR(32),
    IN in_codice_reward VARCHAR(32)
)
BEGIN
    UPDATE FINANZIAMENTO 
    SET codice_reward = in_codice_reward
    WHERE email_utente = in_email_utente 
    AND nome_progetto = in_nome_progetto
    AND codice_reward IS NULL;
END //
DELIMITER ;


-- Procedura per l'inserimento di un commento
DROP PROCEDURE IF EXISTS inserisci_commento;

DELIMITER //
CREATE PROCEDURE inserisci_commento(
    IN in_nome_progetto VARCHAR(32),
    IN in_email_utente VARCHAR(32),
    IN in_testo VARCHAR(255)
)
BEGIN
    INSERT INTO COMMENTO (nome_progetto, email_utente, testo, data)
    VALUES (in_nome_progetto, in_email_utente, in_testo, CURDATE());
END //
DELIMITER ;


-- Procedura per l'inserimento di una candidatura
DROP PROCEDURE IF EXISTS inserisci_candidatura;

DELIMITER //
CREATE PROCEDURE inserisci_candidatura(
    IN in_email_utente VARCHAR(32),
    IN in_id_profilo INT,
    OUT esito BOOLEAN
)
BEGIN
    SELECT NOT EXISTS (
        SELECT *
        FROM SKILL_RICHIESTA sr
        LEFT JOIN SKILL_POSSEDUTA sp 
            ON sr.nome_competenza = sp.nome_competenza
            AND sp.email_utente = in_email_utente
            AND sp.livello >= sr.livello
        WHERE sr.id_profilo = in_id_profilo
        AND sp.email_utente IS NULL
    ) INTO esito;

    IF esito THEN
        INSERT INTO CANDIDATURA (email_utente, id_profilo, stato)
        VALUES (in_email_utente, in_id_profilo, 'IN ATTESA');
    END IF;
END //
DELIMITER ;





-- SOLO AMMINISTRATORI ------------------------------------------------------------


-- Procedura per l'autenticazione (solo amministratori)
DROP PROCEDURE IF EXISTS autenticazione_amministratore;

DELIMITER //
CREATE PROCEDURE autenticazione_amministratore(
    IN in_email VARCHAR(32),
    IN in_password CHAR(64),
    IN in_codice_sicurezza CHAR(64),
    OUT esito BOOLEAN
)
BEGIN
    SELECT COUNT(*) > 0 INTO esito
    FROM UTENTE u
    JOIN UTENTE_AMMINISTRATORE ua ON u.email = ua.email_utente
    WHERE u.email = in_email 
    AND u.password = in_password 
    AND ua.codice_sicurezza = in_codice_sicurezza;
END //
DELIMITER ;


-- Procedura per l'inserimento di una nuova competenza (solo amministratori)
DROP PROCEDURE IF EXISTS aggiungi_competenza;

DELIMITER //
CREATE PROCEDURE aggiungi_competenza(
    IN in_competenza VARCHAR(32),
    IN in_email VARCHAR(32),
    IN in_codice_sicurezza CHAR(64),
    OUT is_amministratore BOOLEAN
)
BEGIN
    CALL verifica_amministratore(in_email, in_codice_sicurezza, is_amministratore);
    
    IF is_amministratore THEN
        INSERT INTO COMPETENZA (nome)
        VALUES (in_competenza);
    END IF;
END //
DELIMITER ;





-- SOLO CREATORI ------------------------------------------------------------


-- Procedura per l'inserimento di un nuovo progetto (solo creatori)
DROP PROCEDURE IF EXISTS crea_progetto;

DELIMITER //
CREATE PROCEDURE crea_progetto(
    IN in_nome VARCHAR(32),
    IN in_descrizione VARCHAR(255),
    IN in_budget DECIMAL(16,2),
    IN in_data_limite DATE,
    IN in_tipo ENUM ('SOFTWARE', 'HARDWARE'),
    IN in_email_creatore VARCHAR(32),
    OUT esito BOOLEAN
)
BEGIN
    DECLARE is_creatore BOOLEAN;
    CALL verifica_creatore(in_email_creatore, is_creatore);

    SET esito = is_creatore AND in_data_limite > CURDATE();
    
    IF esito THEN
        INSERT INTO PROGETTO (nome, descrizione, budget, data_inserimento, data_limite, stato, tipo, email_utente_creatore)
        VALUES (in_nome, in_descrizione, in_budget, CURDATE(), in_data_limite, 'APERTO', in_tipo, in_email_creatore);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una foto per un progetto (solo creatore del progetto)
DROP PROCEDURE IF EXISTS inserisci_foto;

DELIMITER //
CREATE PROCEDURE inserisci_foto(
    IN in_immagine LONGBLOB,
    IN in_nome_progetto VARCHAR(32),
    IN in_email_creatore VARCHAR(32),
    OUT esito BOOLEAN
)
BEGIN
    DECLARE is_creatore_progetto BOOLEAN;
    DECLARE is_progetto_aperto BOOLEAN;
    
    CALL verifica_creatore_progetto(in_nome_progetto, in_email_creatore, is_creatore_progetto);
    CALL verifica_progetto_aperto(in_nome_progetto, is_progetto_aperto);

    SET esito = is_creatore_progetto AND is_progetto_aperto;

    IF esito THEN
        INSERT INTO FOTO (immagine, nome_progetto)
        VALUES (in_immagine, in_nome_progetto);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una reward (solo creatore del progetto)
DROP PROCEDURE IF EXISTS inserisci_reward;

DELIMITER //
CREATE PROCEDURE inserisci_reward(
    IN in_codice VARCHAR(32),
    IN in_immagine LONGBLOB,
    IN in_descrizione VARCHAR(255),
    IN in_nome_progetto VARCHAR(32),
    IN in_email_creatore VARCHAR(32),
    OUT esito BOOLEAN
)
BEGIN
    DECLARE is_creatore_progetto BOOLEAN;
    DECLARE is_progetto_aperto BOOLEAN;
    
    CALL verifica_creatore_progetto(in_nome_progetto, in_email_creatore, is_creatore_progetto);
    CALL verifica_progetto_aperto(in_nome_progetto, is_progetto_aperto);

    SET esito = is_creatore_progetto AND is_progetto_aperto;
    
    IF esito THEN
        INSERT INTO REWARD (codice, immagine, descrizione, nome_progetto)
        VALUES (in_codice, in_immagine, in_descrizione, in_nome_progetto);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una risposta ad un commento (solo creatore del progetto)
DROP PROCEDURE IF EXISTS inserisci_risposta;

DELIMITER //
CREATE PROCEDURE inserisci_risposta(
    IN in_id_commento INT,
    IN in_testo VARCHAR(255),
    IN in_email_creatore VARCHAR(32),
    OUT is_creatore_progetto BOOLEAN
)
BEGIN
    CALL verifica_creatore_progetto(
        (SELECT c.nome_progetto FROM COMMENTO c WHERE c.id = in_id_commento),
        in_email_creatore,
        is_creatore_progetto);

    IF is_creatore_progetto THEN
        INSERT INTO RISPOSTA (id_commento, testo)
        VALUES (in_id_commento, in_testo);
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di un profilo (solo creatore del progetto SOFTWARE)
DROP PROCEDURE IF EXISTS inserisci_profilo;

DELIMITER //
CREATE PROCEDURE inserisci_profilo(
    IN in_nome VARCHAR(32),
    IN in_nome_progetto VARCHAR(32),
    IN in_email_creatore VARCHAR(32),
    OUT is_creatore_progetto BOOLEAN
)
BEGIN
    CALL verifica_creatore_progetto(in_nome_progetto, in_email_creatore, is_creatore_progetto);

    IF is_creatore_progetto THEN
        CALL verifica_tipo_progetto(in_nome_progetto, 'SOFTWARE', is_creatore_progetto);
        IF is_creatore_progetto THEN
            INSERT INTO PROFILO (nome, nome_progetto)
            VALUES (in_nome, in_nome_progetto);
        END IF;
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una skill richiesta (solo creatore del progetto SOFTWARE)
DROP PROCEDURE IF EXISTS inserisci_skill_richiesta;

DELIMITER //
CREATE PROCEDURE inserisci_skill_richiesta(
    IN in_id_profilo INT,
    IN in_email_creatore VARCHAR(32),
    IN in_competenza VARCHAR(32),
    IN in_livello INT,
    OUT is_creatore_progetto BOOLEAN
)
BEGIN
    DECLARE nome_progetto VARCHAR(32);

    SELECT pr.nome_progetto INTO nome_progetto
    FROM PROFILO pr
    WHERE pr.id = in_id_profilo;

    CALL verifica_creatore_progetto(nome_progetto, in_email_creatore, is_creatore_progetto);

    IF is_creatore_progetto THEN
        CALL verifica_tipo_progetto(nome_progetto, 'SOFTWARE', is_creatore_progetto);
        IF is_creatore_progetto THEN
            INSERT INTO SKILL_RICHIESTA (id_profilo, nome_competenza, livello)
            VALUES (in_id_profilo, in_competenza, in_livello);
        END IF;
    END IF;
END //
DELIMITER ;


-- Procedura per gestire una candidatura (solo creatore del progetto SOFTWARE)
DROP PROCEDURE IF EXISTS gestisci_candidatura;

DELIMITER //
CREATE PROCEDURE gestisci_candidatura(
    IN in_email_candidato VARCHAR(32),
    IN in_id_profilo INT,
    IN in_email_creatore VARCHAR(32),
    IN in_stato ENUM ('ACCETTATA', 'RIFIUTATA'),
    OUT is_creatore_progetto BOOLEAN
)
BEGIN
    DECLARE nome_progetto VARCHAR(32);

    SELECT pr.nome_progetto INTO nome_progetto
    FROM PROFILO pr
    WHERE pr.id = in_id_profilo;

    CALL verifica_creatore_progetto(nome_progetto, in_email_creatore, is_creatore_progetto);

    IF is_creatore_progetto THEN
        CALL verifica_tipo_progetto(nome_progetto, 'SOFTWARE', is_creatore_progetto);

        IF is_creatore_progetto THEN
            UPDATE CANDIDATURA
            SET stato = in_stato
            WHERE email_utente = in_email_candidato
            AND id_profilo = in_id_profilo;
        END IF;
    END IF;
END //
DELIMITER ;


-- Procedura per l'inserimento di una componente su un progetto hardware (solo creatore del progetto HARDWARE)
DROP PROCEDURE IF EXISTS inserisci_composizione;

DELIMITER //
CREATE PROCEDURE inserisci_composizione(
    IN in_nome_componente VARCHAR(32),
    IN in_quantita INT,
    IN in_nome_progetto VARCHAR(32),
    IN in_email_creatore VARCHAR(32),
    OUT is_creatore_progetto BOOLEAN
)
BEGIN
    CALL verifica_creatore_progetto(in_nome_progetto, in_email_creatore, is_creatore_progetto);
    IF is_creatore_progetto THEN
        CALL verifica_tipo_progetto(in_nome_progetto, 'HARDWARE', is_creatore_progetto);
        IF is_creatore_progetto THEN
            INSERT INTO COMPOSIZIONE (nome_progetto, nome_componente, quantita)
            VALUES (in_nome_progetto, in_nome_componente, in_quantita);
        END IF;
    END IF;
END //


-- Procedura per l'inserimento di una nuova componente (solo creatori)
DROP PROCEDURE IF EXISTS inserisci_componente;

DELIMITER //
CREATE PROCEDURE inserisci_componente(
    IN in_nome VARCHAR(32),
    IN in_descrizione VARCHAR(255),
    IN in_prezzo DECIMAL(16,2),
    IN in_email_creatore VARCHAR(32),
    OUT esito BOOLEAN
)
BEGIN
    DECLARE is_creatore BOOLEAN;
    CALL verifica_creatore(in_email_creatore, is_creatore);

    IF is_creatore THEN
        INSERT INTO COMPONENTE (nome, descrizione, prezzo)
        VALUES (in_nome, in_descrizione, in_prezzo);
        SET esito = TRUE;
    ELSE
        SET esito = FALSE;
    END IF;
END //
DELIMITER ;
