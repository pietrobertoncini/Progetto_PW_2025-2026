CREATE DATABASE play_room_planner;
USE play_room_planner;

CREATE TABLE DOTAZIONE_DI_SUPPORTO (
    id_dotazione INT PRIMARY KEY AUTO_INCREMENT,
    tipo VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE SETTORE (
    id_settore INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL UNIQUE,
    tipo VARCHAR(50) NOT NULL CHECK (tipo IN ('musica', 'teatro', 'ballo')),
    num_iscritti INT DEFAULT 0,
    id_responsabile INT NULL UNIQUE
);

CREATE TABLE UTENTE (
    id_utente INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    data_nascita DATE NOT NULL,
    ruolo VARCHAR(20) NULL CHECK (ruolo IN ('docente', 'allievo', 'tecnico')),
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    foto VARCHAR(255) NULL,
    
    anni_servizio INT NULL CHECK (anni_servizio IS NULL OR anni_servizio >= 0),
    data_nomina DATE NULL,
    is_responsabile BOOLEAN NOT NULL DEFAULT FALSE,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    
    id_settore INT NULL,
    FOREIGN KEY (id_settore) REFERENCES SETTORE(id_settore)
        ON DELETE NO ACTION -- impedisce la cancellazione di settori con iscritti
        ON UPDATE CASCADE
);

-- aggiunta della FOREIGN KEY mancante a SETTORE
ALTER TABLE SETTORE
ADD CONSTRAINT fk_settore_responsabile
    FOREIGN KEY (id_responsabile) REFERENCES UTENTE(id_utente)
    ON DELETE NO ACTION -- impedisce l'eliminazione di un UTENTE se è responsabile di un settore
    ON UPDATE CASCADE;

CREATE TABLE SALA (
    id_settore INT NOT NULL,
    nome_sala VARCHAR(50) NOT NULL,
    capienza_max INT NOT NULL CHECK (capienza_max > 0),
    
    PRIMARY KEY (id_settore, nome_sala),
    FOREIGN KEY (id_settore) REFERENCES SETTORE(id_settore)
        ON DELETE CASCADE -- se il settore è cancellato, le sue sale sono cancellate
        ON UPDATE CASCADE
);

CREATE TABLE PRENOTAZIONE (
    id_settore INT NOT NULL,
    nome_sala VARCHAR(50) NOT NULL,
    data DATE NOT NULL,
    ora INT NOT NULL CHECK (ora BETWEEN 9 AND 23),
    
    durata INT NOT NULL CHECK (durata > 0),
    attivita VARCHAR(255) NULL,
    id_organizzatore INT NOT NULL,
    
    PRIMARY KEY (id_settore, nome_sala, data, ora),
    
    FOREIGN KEY (id_settore, nome_sala) REFERENCES SALA(id_settore, nome_sala)
        ON DELETE NO ACTION -- non cancellare una sala se ha prenotazioni
        ON UPDATE CASCADE,
    FOREIGN KEY (id_organizzatore) REFERENCES UTENTE(id_utente)
        ON DELETE NO ACTION -- non cancellare un responsabile se ha prenotazioni
        ON UPDATE CASCADE
);

CREATE TABLE INVITO (
    id_utente INT NOT NULL,
    
    id_settore INT NOT NULL,
    nome_sala VARCHAR(50) NOT NULL,
    data DATE NOT NULL,
    ora INT NOT NULL,
    
    stato VARCHAR(20) NOT NULL DEFAULT 'invitato' CHECK (stato IN ('invitato', 'accettato', 'rifiutato')),
    motivazione VARCHAR(255) NULL,
    data_risposta TIMESTAMP NULL,
    
    PRIMARY KEY (id_utente, id_settore, nome_sala, data, ora),
    
    FOREIGN KEY (id_utente) REFERENCES UTENTE(id_utente)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (id_settore, nome_sala, data, ora) REFERENCES PRENOTAZIONE(id_settore, nome_sala, data, ora)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE SALA_DOTAZIONE (
    id_settore INT NOT NULL,
    nome_sala VARCHAR(50) NOT NULL,
    id_dotazione INT NOT NULL,
    
    PRIMARY KEY (id_settore, nome_sala, id_dotazione),
    FOREIGN KEY (id_settore, nome_sala) REFERENCES SALA(id_settore, nome_sala)
        ON DELETE CASCADE -- se la sala viene eliminata, le sue associazioni con le dotazioni devono essere eliminate
        ON UPDATE CASCADE,
    FOREIGN KEY (id_dotazione) REFERENCES DOTAZIONE_DI_SUPPORTO(id_dotazione)
        ON DELETE NO ACTION -- impedisce l'eliminazione della dotazione se è assegnata a qualche sala
        ON UPDATE CASCADE
);

-- 3. POPOLAMENTO DELLE TABELLE 

INSERT INTO DOTAZIONE_DI_SUPPORTO (id_dotazione, tipo) VALUES
(1, 'Pianoforte a coda'),
(2, 'Proiettore HD'),
(3, 'Parete a specchi'),
(4, 'Impianto audio surround'),
(5, 'Mixer Audio');

INSERT INTO SETTORE (id_settore, nome, tipo, num_iscritti, id_responsabile) VALUES
(1, 'Dipartimento di Musica', 'musica', 0, NULL),
(2, 'Accademia di Teatro', 'teatro', 0, NULL),
(3, 'Scuola di Ballo', 'ballo', 0, NULL);

INSERT INTO UTENTE (id_utente, nome, cognome, data_nascita, ruolo, email, password_hash, foto, id_settore, is_responsabile, is_admin) VALUES
-- ADMIN 
(100, 'Super', 'Admin', '1980-01-01', NULL, 'admin@playroom.it', '$2y$10$hQRtNiBLRnsp7uo4HXSVB./.arZ6Ju10C1WV4dtSFP7gmy8waLzWG', NULL, NULL, FALSE, TRUE),

(101, 'Mario', 'Rossi', '1980-05-15', 'docente', 'mario.rossi@email.it', '$2y$10$hQRtNiBLRnsp7uo4HXSVB./.arZ6Ju10C1WV4dtSFP7gmy8waLzWG', NULL, 1, FALSE, FALSE),
(102, 'Anna', 'Bianchi', '1990-07-20', 'tecnico', 'anna.bianchi@email.it', '$2y$10$hQRtNiBLRnsp7uo4HXSVB./.arZ6Ju10C1WV4dtSFP7gmy8waLzWG', NULL, 1, FALSE, FALSE),
(103, 'Luca', 'Verdi', '2002-11-30', 'allievo', 'luca.verdi@email.it', '$2y$10$hQRtNiBLRnsp7uo4HXSVB./.arZ6Ju10C1WV4dtSFP7gmy8waLzWG', NULL, 1, FALSE, FALSE),
(104, 'Giulia', 'Neri', '2003-01-10', 'allievo', 'giulia.neri@email.it', '$2y$10$hQRtNiBLRnsp7uo4HXSVB./.arZ6Ju10C1WV4dtSFP7gmy8waLzWG', NULL, 1, FALSE, FALSE),
(105, 'Paolo', 'Gialli', '1975-02-05', 'docente', 'paolo.gialli@email.it', '$2y$10$hQRtNiBLRnsp7uo4HXSVB./.arZ6Ju10C1WV4dtSFP7gmy8waLzWG', NULL, 2, FALSE, FALSE),
(106, 'Sara', 'Pozzi', '2001-06-25', 'allievo', 'sara.pozzi@email.it', '$2y$10$hQRtNiBLRnsp7uo4HXSVB./.arZ6Ju10C1WV4dtSFP7gmy8waLzWG', NULL, 2, FALSE, FALSE),
(107, 'Franco', 'Miti', '1985-09-12', 'tecnico', 'franco.miti@email.it', '$2y$10$hQRtNiBLRnsp7uo4HXSVB./.arZ6Ju10C1WV4dtSFP7gmy8waLzWG', NULL, 3, FALSE, FALSE),
(108, 'Chiara', 'Blu', '2004-03-18', 'allievo', 'chiara.blu@email.it', '$2y$10$hQRtNiBLRnsp7uo4HXSVB./.arZ6Ju10C1WV4dtSFP7gmy8waLzWG', NULL, 3, FALSE, FALSE);

-- Aggiornamento num_iscritti
UPDATE SETTORE SET num_iscritti = 4 WHERE id_settore = 1;
UPDATE SETTORE SET num_iscritti = 2 WHERE id_settore = 2;
UPDATE SETTORE SET num_iscritti = 2 WHERE id_settore = 3;

-- Promozione dei Responsabili
UPDATE UTENTE SET is_responsabile = TRUE, anni_servizio = 10, data_nomina = '2015-09-01' WHERE id_utente = 101; -- Mario Rossi
UPDATE UTENTE SET is_responsabile = TRUE, anni_servizio = 15, data_nomina = '2010-09-01' WHERE id_utente = 105; -- Paolo Gialli
UPDATE UTENTE SET is_responsabile = TRUE, anni_servizio = 5, data_nomina = '2020-01-15' WHERE id_utente = 107; -- Franco Miti

-- Assegnazione Responsabili ai Settori
UPDATE SETTORE SET id_responsabile = 101 WHERE id_settore = 1; -- Mario Rossi dirige Musica
UPDATE SETTORE SET id_responsabile = 105 WHERE id_settore = 2; -- Paolo Gialli dirige Teatro
UPDATE SETTORE SET id_responsabile = 107 WHERE id_settore = 3; -- Franco Miti dirige Ballo

INSERT INTO SALA (id_settore, nome_sala, capienza_max) VALUES
(1, 'Aula Magna', 100),
(1, 'Sala Prove 1', 20),
(2, 'Palco A', 150),
(3, 'Sala Specchi', 40),
(1, 'Studio Registrazione', 5);

-- (Mix di date passate e future)
INSERT INTO PRENOTAZIONE (id_settore, nome_sala, data, ora, durata, attivita, id_organizzatore) VALUES

-- SETTIMANA CORRENTE (19-25 GENNAIO 2026)
(1, 'Sala Prove 1', '2026-01-19', 10, 2, 'Prove Quartetto d''Archi', 101),
(2, 'Palco A', '2026-01-20', 15, 3, 'Laboratorio Dizione', 105),
(1, 'Studio Registrazione', '2026-01-21', 09, 3, 'Registrazione Singolo', 101),
(3, 'Sala Specchi', '2026-01-21', 19, 2, 'Allenamento Serale', 107),      
(1, 'Aula Magna', '2026-01-22', 10, 4, 'Masterclass Violino', 101),
(2, 'Palco A', '2026-01-22', 16, 4, 'Prove Luci Spettacolo', 105),
(1, 'Sala Prove 1', '2026-01-23', 14, 2, 'Lezione Batteria', 101),
(3, 'Sala Specchi', '2026-01-24', 09, 3, 'Stage Danza Moderna', 107),

-- SETTIMANA ESAME (26 GENNAIO - 1 FEBBRAIO 2026) 
(1, 'Aula Magna', '2026-01-27', 09, 4, 'Discussione Progetti Web', 101),    
(1, 'Sala Prove 1', '2026-01-27', 14, 2, 'Preparazione Orale', 101),
(2, 'Palco A', '2026-01-27', 09, 2, 'Set Fotografico Costumi', 105),
(2, 'Palco A', '2026-01-27', 11, 5, 'Allestimento Scenico Esame', 105),    
(3, 'Sala Specchi', '2026-01-27', 16, 3, 'Rifinitura Coreografie', 107),
(1, 'Studio Registrazione', '2026-01-28', 10, 4, 'Editing Audio Esame', 101),
(2, 'Palco A', '2026-01-29', 14, 6, 'Seminario Recitazione', 105),
(1, 'Aula Magna', '2026-01-31', 18, 3, 'Concerto Inaugurale Gennaio', 101),

-- SETTIMANE INTERMEDIE (POCHE PRENOTAZIONI)
(1, 'Studio Registrazione', '2026-02-04', 15, 2, 'Manutenzione Mixer', 101),
(3, 'Sala Specchi', '2026-02-12', 17, 2, 'Corso Tango Principianti', 107),
(2, 'Palco A', '2026-02-18', 10, 3, 'Lettura Copione', 105),

-- SETTIMANA SECONDO APPELLO (23 FEBBRAIO - 1 MARZO 2026)
(2, 'Palco A', '2026-02-23', 09, 4, 'Audizioni Secondo Appello', 105),
(1, 'Aula Magna', '2026-02-24', 10, 3, 'Prove Corali', 101),
(1, 'Sala Prove 1', '2026-02-25', 14, 2, 'Verifica Strumenti Fiato', 101),
(2, 'Palco A', '2026-02-26', 09, 6, 'Sessione Esami Febbraio', 105),       
(1, 'Studio Registrazione', '2026-02-26', 15, 3, 'Mixaggio Finali Esami', 101),
(3, 'Sala Specchi', '2026-02-26', 18, 2, 'Saggio Breve Ballo', 107),
(1, 'Aula Magna', '2026-02-27', 16, 4, 'Concerto di Fine Sessione', 101),
(3, 'Sala Specchi', '2026-02-28', 10, 3, 'Pulizia Specchi e Sbarre', 107);


-- NOTA TECNICA: 
-- L'applicazione Web crea automaticamente un record nella tabella INVITO per l'organizzatore 
-- quando viene creata una prenotazione (per visualizzarla in "I Miei Impegni").
-- In questo script di popolamento manuale, dobbiamo inserire esplicitamente queste righe 
-- per replicare il comportamento del backend e garantire la coerenza dei dati visualizzati.

-- AUTOINVITI ORGANIZZATORI
INSERT INTO INVITO (id_utente, id_settore, nome_sala, data, ora, stato, data_risposta) VALUES
(101, 1, 'Sala Prove 1', '2026-01-19', 10, 'accettato', '2026-01-18 09:00:00'),
(105, 2, 'Palco A', '2026-01-20', 15, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Studio Registrazione', '2026-01-21', 09, 'accettato', '2026-01-18 09:00:00'),
(107, 3, 'Sala Specchi', '2026-01-21', 19, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Aula Magna', '2026-01-22', 10, 'accettato', '2026-01-18 09:00:00'),
(105, 2, 'Palco A', '2026-01-22', 16, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Sala Prove 1', '2026-01-23', 14, 'accettato', '2026-01-18 09:00:00'),
(107, 3, 'Sala Specchi', '2026-01-24', 09, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Aula Magna', '2026-01-27', 09, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Sala Prove 1', '2026-01-27', 14, 'accettato', '2026-01-18 09:00:00'),
(105, 2, 'Palco A', '2026-01-27', 09, 'accettato', '2026-01-18 09:00:00'),
(105, 2, 'Palco A', '2026-01-27', 11, 'accettato', '2026-01-18 09:00:00'),
(107, 3, 'Sala Specchi', '2026-01-27', 16, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Studio Registrazione', '2026-01-28', 10, 'accettato', '2026-01-18 09:00:00'),
(105, 2, 'Palco A', '2026-01-29', 14, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Aula Magna', '2026-01-31', 18, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Studio Registrazione', '2026-02-04', 15, 'accettato', '2026-01-18 09:00:00'),
(107, 3, 'Sala Specchi', '2026-02-12', 17, 'accettato', '2026-01-18 09:00:00'),
(105, 2, 'Palco A', '2026-02-18', 10, 'accettato', '2026-01-18 09:00:00'),
(105, 2, 'Palco A', '2026-02-23', 09, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Aula Magna', '2026-02-24', 10, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Sala Prove 1', '2026-02-25', 14, 'accettato', '2026-01-18 09:00:00'),
(105, 2, 'Palco A', '2026-02-26', 09, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Studio Registrazione', '2026-02-26', 15, 'accettato', '2026-01-18 09:00:00'),
(107, 3, 'Sala Specchi', '2026-02-26', 18, 'accettato', '2026-01-18 09:00:00'),
(101, 1, 'Aula Magna', '2026-02-27', 16, 'accettato', '2026-01-18 09:00:00'),
(107, 3, 'Sala Specchi', '2026-02-28', 10, 'accettato', '2026-01-18 09:00:00');

-- INVITI PARTECIPANTI AGGIUNTIVI
INSERT INTO INVITO (id_utente, id_settore, nome_sala, data, ora, stato, data_risposta) VALUES
(102, 1, 'Aula Magna', '2026-01-27', 09, 'accettato', '2026-01-21 10:00:00'),
(103, 1, 'Aula Magna', '2026-01-27', 09, 'accettato', '2026-01-21 10:15:00'),
(104, 1, 'Aula Magna', '2026-01-27', 09, 'accettato', '2026-01-21 10:30:00'),
(106, 1, 'Aula Magna', '2026-01-27', 09, 'invitato', NULL), -- In attesa
(108, 1, 'Aula Magna', '2026-01-27', 09, 'rifiutato', '2026-01-21 11:00:00'); -- Rifiutato con motivazione nel DB

INSERT INTO INVITO (id_utente, id_settore, nome_sala, data, ora, stato, data_risposta) VALUES
(106, 2, 'Palco A', '2026-02-26', 09, 'accettato', '2026-01-21 12:00:00'),
(108, 2, 'Palco A', '2026-02-26', 09, 'accettato', '2026-01-21 12:30:00'),
(102, 2, 'Palco A', '2026-02-26', 09, 'invitato', NULL);

INSERT INTO INVITO (id_utente, id_settore, nome_sala, data, ora, stato, data_risposta) VALUES
(103, 1, 'Sala Prove 1', '2026-01-23', 14, 'accettato', '2026-01-21 09:00:00'),
(108, 3, 'Sala Specchi', '2026-01-24', 09, 'accettato', '2026-01-21 09:30:00'),
(104, 1, 'Studio Registrazione', '2026-01-28', 10, 'invitato', NULL),
(102, 1, 'Aula Magna', '2026-01-31', 18, 'accettato', '2026-01-21 15:00:00');

INSERT INTO SALA_DOTAZIONE (id_settore, nome_sala, id_dotazione) VALUES
(1, 'Aula Magna', 1),
(1, 'Aula Magna', 2),
(1, 'Aula Magna', 4),
(1, 'Sala Prove 1', 1),
(3, 'Sala Specchi', 3),
(3, 'Sala Specchi', 4),
(1, 'Studio Registrazione', 4),
(1, 'Studio Registrazione', 5);