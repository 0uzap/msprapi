# Projet MSPR - API Node.js avec MySQL et Docker

## Description
Ce projet est une API Node.js utilisant Express et MySQL, entièrement dockerisée. La base de données est en MySQL et PhpMyAdmin.

---

## Prérequis
Avant de commencer, assurez-vous d'avoir installé les outils suivants :

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

---

## Installation et Démarrage
### 1. Cloner le projet
```bash
git clone <URL_DU_REPO>
cd msprapi
npm install
```

### 2. Construire et démarrer les conteneurs
- Pour construire les images et démarrer les conteneurs :
```bash
docker-compose up --build
```
- Si les images sont déjà construites, démarrer simplement les services :
```bash
docker-compose up
```
- Pour exécuter les conteneurs en arrière-plan (mode détaché) :
```bash
docker-compose up -d
```

---
## Gestion de la Base de Données
### 1. Importer les tables dans la base de données
- Copier le fichier SQL dans le conteneur MySQL :
```bash
docker cp create_tables.sql mspr-db-1:/tmp/create_tables.sql
```
- Exécuter le script SQL pour créer les tables :
```bash
docker exec -i mspr-db-1 sh -c 'cat /tmp/create_tables.sql | mysql -uroot -prootpassword bdd_mspr_api'
```
- Vérifier que les tables sont bien créées :
```bash
docker exec -it mspr-db-1 mysql -uroot -prootpassword -e "SHOW TABLES;" bdd_mspr_api
```

---

## Importation des Données CSV
### 1. Installer les dépendances à l'intérieur du conteneur
```bash
npm install mysql2 csv-parser fs
```
### 2. Copier les fichiers CSV dans le conteneur API
```bash
docker cp import_csv.js mspr-api-1:/usr/src/app/import_csv.js

# Créer le dossier de stockage et y copier les fichiers

docker exec -it mspr-api-1 sh -c "mkdir -p /mnt/data"
docker cp country_wise_latest.csv mspr-api-1:/mnt/data/country_wise_latest.csv
docker cp owid-monkeypox-data.csv mspr-api-1:/mnt/data/owid-monkeypox-data.csv
docker cp worldometer_coronavirus_daily_data.csv mspr-api-1:/mnt/data/worldometer_coronavirus_daily_data.csv
```
### 3. Vérifier que les fichiers sont bien présents
```bash
docker exec -it mspr-api-1 sh -c "ls /mnt/data"
```
### 4. Exécuter le script d'importation des données
```bash
docker exec -it mspr-api-1 bash -c "node import_csv.js"
```

---
---

## Accès aux Services
- **Documentation Swagger** : [http://localhost:3002/api-docs](http://localhost:3002/api-docs)
- **PhpMyAdmin** : [http://localhost:8080](http://localhost:8080)

---


