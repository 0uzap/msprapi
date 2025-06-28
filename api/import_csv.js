const mysql = require('mysql2');
const fs = require('fs');
const csv = require('csv-parser');

const pays = process.env.PAYS_CIBLE || 'FR';

let dbHost = 'db'; // par défaut
if (pays === 'FR') dbHost = 'db-fr';
if (pays === 'US') dbHost = 'db-us';
if (pays === 'CH') dbHost = 'db-ch';

const connection = mysql.createConnection({
  host: dbHost,
// const connection = mysql.createConnection({
//   host: 'db', 
  user: 'root',
  password: 'rootpassword',
  database: 'bdd_mspr_api'
});

const importCSV = (filePath,tableName, columns) => {
    return new Promise((resolve, reject) => {
        const batchSize = 10000;
        let batch = [];

        fs.createReadStream(filePath)
            .pipe(csv())
            .on('data', (row) => {
                const values = columns.map(col => row[col] || null);
                batch.push(values);

                if (batch.length === batchSize) {
                    insertBatch(tableName, columns, batch);
                    batch = [];
                }
            })
            .on('end', () => {
                if (batch.length > 0) insertBatch(tableName, columns, batch);
                console.log(`Import terminé pour ${tableName}`);
                resolve();
            })
            .on('error', (error) => reject(error));
    });
};

const insertBatch = (table, columns, batch) => {
    const placeholders = batch.map(() => `(${columns.map(() => '?').join(', ')})`).join(', ');
    const sql = `INSERT INTO ${table} (${columns.join(', ')}) VALUES ${placeholders}`;
    const values = batch.flat();

    connection.query(sql, values, (err) => {
        if (err) {
            console.error(`❌ Erreur d'insertion dans ${table}:`, err.message);
            console.log("⛔ Voici un exemple de données provoquant l'erreur:", batch[0]);
        }
    });
    
};

(async () => {
    try {
        console.log("Importation en cours...");

        await importCSV('/mnt/data/continent.csv', 'continent', [
            'idContinent', 'continent'
        ]);

        await importCSV('/mnt/data/pays.csv', 'pays', [
            'id_pays', 'pays', 'idContinent'
        ]);

        await importCSV('/mnt/data/owid-monkeypox-data-final.csv', 'monkeypox_data', [
            'date', 'nbCasTotaux', 'nbMortTotaux', 'nbNouveauCas', 'nbNouvelleMort',
            'moyenneNouveauCas', 'moyenneNouvelleMort', 'id_pays', 'idContinent'
        ]);

        await importCSV('/mnt/data/worldometer_coronavirus_daily_data_final.csv', 'coronavirus_daily', [
            'date', 'cumulCasTotaux', 'nouveauCasJournalier', 'casActif',
            'cumulMortTotaux', 'nouvelleMortJournaliere', 'id_pays', 'idContinent'
        ]);

        await importCSV('/mnt/data/country_wise_latest_modified_final.csv', 'covid_country', [
            'nbCas', 'nbMort', 'nbSoigne', 'nbActif', 'nbNewCas', 'nbNewMort', 'nbNewSoigne',
            'mort100cas', 'soigne100cas', 'mort100soigne', 'derniereSemaineConfirme',
            'changementSemaine', 'pourcentageAugmentationSemaine', 'continent',
            'id_pays', 'idContinent'
        ]);

        // await importCSV('/mnt/data/country_wise_latest.csv', 'covid_country', [
        //     'country_region', 'confirmed', 'deaths', 'recovered', 'active',
        //     'new_cases', 'new_deaths', 'new_recovered', 'deaths_per_100_cases',
        //     'recovered_per_100_cases', 'deaths_per_100_recovered', 'confirmed_last_week',
        //     'one_week_change', 'one_week_percentage_increase', 'who_region'
        // ]);              

        // await importCSV('/mnt/data/owid-monkeypox-data.csv', 'monkeypox_data', [
        //     'location', 'iso_code', 'date', 'total_cases', 'total_deaths',
        //     'new_cases', 'new_deaths', 'new_cases_smoothed', 'new_deaths_smoothed',
        //     'new_cases_per_million', 'total_cases_per_million',
        //     'new_cases_smoothed_per_million', 'new_deaths_per_million',
        //     'total_deaths_per_million', 'new_deaths_smoothed_per_million'
        // ]);

        // await importCSV('/mnt/data/worldometer_coronavirus_daily_data.csv', 'coronavirus_daily', [
        //     'date', 'country', 'cumulative_total_cases', 'daily_new_cases',
        //     'active_cases', 'cumulative_total_deaths', 'daily_new_deaths'
        // ]);

        console.log("Tous les fichiers ont été importés !");
        connection.end();
    } catch (error) {
        console.error("Erreur d'importation:", error);
        connection.end();
    }
})();