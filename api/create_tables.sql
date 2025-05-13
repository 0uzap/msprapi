-- CREATE DATABASE IF NOT EXISTS bdd_mspr_api;
-- USE bdd_mspr_api;

-- -- Table pour country_wise_latest.csv
-- CREATE TABLE IF NOT EXISTS covid_country (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     country_region VARCHAR(255),
--     confirmed INT,
--     deaths INT,
--     recovered INT,
--     active INT,
--     new_cases INT,
--     new_deaths INT,
--     new_recovered INT,
--     deaths_per_100_cases FLOAT,
--     recovered_per_100_cases FLOAT,
--     deaths_per_100_recovered FLOAT,
--     confirmed_last_week INT,
--     one_week_change INT,
--     one_week_percentage_increase FLOAT,
--     who_region VARCHAR(255)
-- );

-- -- Table pour owid-monkeypox-data.csv
-- CREATE TABLE IF NOT EXISTS monkeypox_data (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     location VARCHAR(255),
--     iso_code VARCHAR(10),
--     date DATE,
--     total_cases INT,
--     total_deaths INT,
--     new_cases INT,
--     new_deaths INT,
--     new_cases_smoothed FLOAT,
--     new_deaths_smoothed FLOAT,
--     new_cases_per_million FLOAT,
--     total_cases_per_million FLOAT,
--     new_cases_smoothed_per_million FLOAT,
--     new_deaths_per_million FLOAT,
--     total_deaths_per_million FLOAT,
--     new_deaths_smoothed_per_million FLOAT
-- );

-- -- Table pour worldometer_coronavirus_daily_data.csv
-- CREATE TABLE IF NOT EXISTS coronavirus_daily (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     date DATE,
--     country VARCHAR(255),
--     cumulative_total_cases INT,
--     daily_new_cases INT,
--     active_cases INT,
--     cumulative_total_deaths INT,
--     daily_new_deaths INT
-- );


CREATE DATABASE IF NOT EXISTS bdd_mspr_api;
USE bdd_mspr_api;

-- Table des continents
CREATE TABLE IF NOT EXISTS continent (
    idContinent INT PRIMARY KEY,
    continent VARCHAR(100)
);

-- Table des pays
CREATE TABLE IF NOT EXISTS pays (
    id_pays INT PRIMARY KEY,
    pays VARCHAR(100),
    idContinent INT,
    FOREIGN KEY (idContinent) REFERENCES continent(idContinent)
);

-- Table des données monkeypox
CREATE TABLE IF NOT EXISTS monkeypox_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE,
    nbCasTotaux INT,
    nbMortTotaux INT,
    nbNouveauCas INT,
    nbNouvelleMort INT,
    moyenneNouveauCas FLOAT,
    moyenneNouvelleMort FLOAT,
    id_pays INT,
    idContinent INT,
    FOREIGN KEY (id_pays) REFERENCES pays(id_pays),
    FOREIGN KEY (idContinent) REFERENCES continent(idContinent)
);

-- Table des données quotidiennes covid (worldometer)
CREATE TABLE IF NOT EXISTS coronavirus_daily (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE,
    cumulCasTotaux INT,
    nouveauCasJournalier INT,
    casActif INT,
    cumulMortTotaux INT,
    nouvelleMortJournaliere INT,
    id_pays INT,
    idContinent INT,
    FOREIGN KEY (id_pays) REFERENCES pays(id_pays),
    FOREIGN key (idContinent) REFERENCES continent(idContinent)
);

-- Table des données COVID globales
CREATE TABLE IF NOT EXISTS covid_country (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nbCas INT,
    nbMort INT,
    nbSoigne INT,
    nbActif INT,
    nbNewCas INT,
    nbNewMort INT, 
    nbNewSoigne INT,
    mort100cas FLOAT,
    soigne100cas FLOAT,
    mort100soigne FLOAT,
    derniereSemaineConfirme INT,
    changementSemaine INT,
    pourcentageAugmentationSemaine FLOAT,
    continent VARCHAR(100),
    id_pays INT,
    idContinent INt,
    FOREIGN KEY (id_pays) REFERENCES pays(id_pays),
    FOREIGN KEY (idContinent) REFERENCES continent(idContinent)
);