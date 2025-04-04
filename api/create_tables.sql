CREATE DATABASE IF NOT EXISTS bdd_mspr_api;
USE bdd_mspr_api;

-- Table pour country_wise_latest.csv
CREATE TABLE IF NOT EXISTS covid_country (
    id INT AUTO_INCREMENT PRIMARY KEY,
    country_region VARCHAR(255),
    confirmed INT,
    deaths INT,
    recovered INT,
    active INT,
    new_cases INT,
    new_deaths INT,
    new_recovered INT,
    deaths_per_100_cases FLOAT,
    recovered_per_100_cases FLOAT,
    deaths_per_100_recovered FLOAT,
    confirmed_last_week INT,
    one_week_change INT,
    one_week_percentage_increase FLOAT,
    who_region VARCHAR(255)
);

-- Table pour owid-monkeypox-data.csv
CREATE TABLE IF NOT EXISTS monkeypox_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(255),
    iso_code VARCHAR(10),
    date DATE,
    total_cases INT,
    total_deaths INT,
    new_cases INT,
    new_deaths INT,
    new_cases_smoothed FLOAT,
    new_deaths_smoothed FLOAT,
    new_cases_per_million FLOAT,
    total_cases_per_million FLOAT,
    new_cases_smoothed_per_million FLOAT,
    new_deaths_per_million FLOAT,
    total_deaths_per_million FLOAT,
    new_deaths_smoothed_per_million FLOAT
);

-- Table pour worldometer_coronavirus_daily_data.csv
CREATE TABLE IF NOT EXISTS coronavirus_daily (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE,
    country VARCHAR(255),
    cumulative_total_cases INT,
    daily_new_cases INT,
    active_cases INT,
    cumulative_total_deaths INT,
    daily_new_deaths INT
);
