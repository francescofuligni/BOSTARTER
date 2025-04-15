USE bostarter_db;


-- Vista per la classifica dei 3 migliori utenti creatori per affidabilità
DROP VIEW IF EXISTS classifica_creatori;

CREATE VIEW classifica_creatori AS
SELECT u.nickname, uc.affidabilita
FROM UTENTE u
JOIN UTENTE_CREATORE uc ON u.email = uc.email_utente
ORDER BY uc.affidabilita DESC
LIMIT 3;


-- Vista per i 3 progetti aperti più vicini al completamento
DROP VIEW IF EXISTS progetti_in_scadenza;

CREATE VIEW progetti_in_scadenza AS
SELECT 
    p.nome,
    (p.budget - COALESCE(SUM(f.importo), 0)) as differenza_budget
FROM PROGETTO p
JOIN FINANZIAMENTO f ON p.nome = f.nome_progetto
WHERE p.stato = 'APERTO'
ORDER BY differenza_budget ASC
LIMIT 3;


-- Vista per la classifica dei 3 migliori utenti per totale finanziamenti erogati
DROP VIEW IF EXISTS classifica_finanziatori;

CREATE VIEW classifica_finanziatori AS
SELECT 
    u.nickname,
    COALESCE(SUM(f.importo), 0) as tot_finanziamenti
FROM UTENTE u
JOIN FINANZIAMENTO f ON u.email = f.email_utente
ORDER BY tot_finanziamenti DESC
LIMIT 3;


--- Vista per tutti i progetti con la prima foto associata --
DROP VIEW IF EXISTS progetti_con_foto;

CREATE VIEW progetti_con_foto AS
WITH prima_foto AS (
    SELECT f.immagine, f.nome_progetto
    FROM FOTO f
    WHERE f.id = (
        SELECT MIN(f2.id)
        FROM FOTO f2
        WHERE f2.nome_progetto = f.nome_progetto
    )
)
SELECT 
    p.*,
    pf.immagine
FROM PROGETTO p
LEFT JOIN prima_foto pf
    ON p.nome = pf.nome_progetto;


-- Vista per tutti i progetti aperti --
DROP VIEW IF EXISTS progetti_aperti;

CREATE VIEW progetti_aperti AS
SELECT p.nome, p.descrizione, p.budget, p.tipo, p.email_utente_creatore, p.immagine 
FROM progetti_con_foto p
WHERE p.stato = 'APERTO';
