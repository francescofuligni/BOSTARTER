CREATE DATABASE IF NOT EXISTS bostarter_db;
USE bostarter_db;

CREATE TABLE IF NOT EXISTS UTENTE (
    email VARCHAR(32) PRIMARY KEY,
    password VARCHAR(32) NOT NULL,
    nome VARCHAR(32) NOT NULL,
    cognome VARCHAR(32) NOT NULL,
    nickname VARCHAR(32) UNIQUE NOT NULL,
    luogo_nascita VARCHAR(32) NOT NULL,
    anno_nascita INT NOT NULL
);

CREATE TABLE IF NOT EXISTS COMPETENZA (
    nome VARCHAR(32) PRIMARY KEY
);

CREATE TABLE IF NOT EXISTS UTENTE_CREATORE (
  email_utente VARCHAR(32) PRIMARY KEY REFERENCES UTENTE(email) ,
  affidabilita INT DEFAULT 0 CHECK (affidabilita >= 0 AND affidabilita <= 5)
);

CREATE TABLE IF NOT EXISTS UTENTE_AMMINISTRATORE (
  email_utente VARCHAR(32) PRIMARY KEY REFERENCES UTENTE(email) ,
  codice_sicurezza CHAR(8) NOT NULL
);

/*
    email_utente_creatore è la chiave esterna per la tabella PROGETTO 
    ma non è UNIQUE perhcè un utente puo' creare piu' progetti.
*/

CREATE TABLE IF NOT EXISTS PROGETTO (
    nome VARCHAR(32) PRIMARY KEY,
    descrizione VARCHAR(255) NOT NULL,
    budget DECIMAL(16,2) NOT NULL,
    data_inserimento DATE NOT NULL,
    data_limite DATE NOT NULL,
    stato ENUM ('APERTO', 'CHIUSO') NOT NULL,
    tipo ENUM ('SOFTWARE', 'HARDWARE') NOT NULL,
    email_utente_creatore VARCHAR(32) NOT NULL REFERENCES UTENTE_CREATORE(email_utente)
);

CREATE TABLE IF NOT EXISTS FOTO (
    immagine BLOB NOT NULL,
    nome_progetto VARCHAR(32) NOT NULL REFERENCES PROGETTO(nome),
    PRIMARY KEY (immagine, nome_progetto)
);