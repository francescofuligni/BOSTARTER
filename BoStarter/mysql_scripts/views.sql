USE bostarter_db;


-- Vista per tutti i progetti con la prima foto associata
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


-- Vista per la classifica dei 3 migliori utenti creatori per affidabilità
DROP VIEW IF EXISTS classifica_creatori;

CREATE VIEW classifica_creatori AS
SELECT u.nickname, uc.affidabilita
FROM UTENTE u
JOIN UTENTE_CREATORE uc ON u.email = uc.email_utente
WHERE u.nickname IS NOT NULL
  AND u.nickname != ''
ORDER BY uc.affidabilita DESC
LIMIT 3;


-- Vista per i 3 progetti aperti più vicini al completamento
DROP VIEW IF EXISTS progetti_in_scadenza;

CREATE VIEW progetti_in_scadenza AS
SELECT 
    p.nome, p.immagine,
    (p.budget - COALESCE(SUM(f.importo), 0)) as differenza_budget
FROM progetti_con_foto p
JOIN FINANZIAMENTO f ON p.nome = f.nome_progetto
WHERE p.stato = 'APERTO'
  AND p.nome IS NOT NULL
  AND p.nome != ''
GROUP BY p.nome, p.budget
ORDER BY differenza_budget ASC
LIMIT 3;


-- Vista per la classifica dei 3 migliori utenti per totale finanziamenti erogati
DROP VIEW IF EXISTS classifica_finanziatori;

CREATE VIEW classifica_finanziatori AS
SELECT 
    u.nickname,
    COALESCE(SUM(f.importo), 0) AS tot_finanziamenti
FROM UTENTE u
JOIN FINANZIAMENTO f ON u.email = f.email_utente
WHERE u.nickname IS NOT NULL
  AND u.nickname != ''
GROUP BY u.nickname
ORDER BY tot_finanziamenti DESC
LIMIT 3;


-- Vista per tutti i progetti aperti
DROP VIEW IF EXISTS progetti_aperti;

CREATE VIEW progetti_aperti AS
SELECT p.nome, p.descrizione, p.budget, p.tipo, p.email_utente_creatore, p.immagine 
FROM progetti_con_foto p
WHERE p.stato = 'APERTO';


-- Vista per tutte le foto di ogni progetto
DROP VIEW IF EXISTS foto_progetto;

CREATE VIEW foto_progetto AS
SELECT nome_progetto, immagine
FROM FOTO;


-- Vista per tutti i commenti di ogni progetto (con nickname)
DROP VIEW IF EXISTS commenti_progetto;

CREATE VIEW commenti_progetto AS
SELECT 
    c.id,
    c.nome_progetto,
    c.testo,
    u.nickname,
    c.data,
    r.testo AS risposta
FROM COMMENTO c
JOIN UTENTE u ON c.email_utente = u.email
LEFT JOIN RISPOSTA r ON c.id = r.id_commento
ORDER BY c.data DESC;


