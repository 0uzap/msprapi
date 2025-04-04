const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');
const swaggerUi = require('swagger-ui-express');
const YAML = require('yamljs');

const swaggerDocument = YAML.load('./api/swagger.yaml');
console.log('📄 Swagger chargé avec succès');

const app = express();
const port = process.env.PORT || 3001;

app.use(cors());
app.use(express.json()); 

app.use((req, res, next) => {
    res.header("Access-Control-Allow-Origin", "*");
    res.header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
    res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    next();
});


app.options("*", (req, res) => res.sendStatus(200));

const connection = mysql.createConnection({
    host: 'db',  
    user: 'root',
    password: 'rootpassword',
    database: 'bdd_mspr_api',
    port: 3306
  });

const connectWithRetry = () => {
  connection.connect((err) => {
    if (err) {
      console.error('❌ Erreur de connexion à MySQL:', err.message);
      console.log('🔄 Nouvelle tentative de connexion dans 5 secondes...');
      setTimeout(connectWithRetry, 5000);
    } else {
      console.log('✅ Connecté à la base de données MySQL');
    }
  });
};

connectWithRetry();

app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(swaggerDocument));

app.get('/', (req, res) => {
  res.send('API COVID-19 Node.js avec MYSQL');
});

// CRUD pour covid_country
app.get('/covid_country', (req, res) => {
    connection.query('SELECT * FROM covid_country', (err, results) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json(results);
    });
});

app.get('/covid_country/:id', (req, res) => {
    connection.query('SELECT * FROM covid_country WHERE id=?', [req.params.id], (err, results) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json(results[0] || {});
    });
});

app.post('/covid_country', (req, res) => {
    const { country_region, confirmed } = req.body;
    if (!country_region || !confirmed) {
        return res.status(400).json({ error: "Certains champs obligatoires sont manquants." });
    }

    connection.query(
      'INSERT INTO covid_country SET ?', req.body,
      (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.status(201).json({ id: results.insertId, ...req.body });
      }
    );
});

app.put('/covid_country/:id', (req, res) => {
    connection.query('UPDATE covid_country SET ? WHERE id=?', [req.body, req.params.id], (err) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json({ message: 'Donnée mise à jour avec succès' });
    });
});

app.delete('/covid_country/:id', (req, res) => {
    connection.query('DELETE FROM covid_country WHERE id=?', [req.params.id], (err) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json({ message: 'Donnée supprimée avec succès' });
    });
});

// CRUD complet pour monkeypox_data
app.get('/monkeypox_data', (req, res) => {
    connection.query('SELECT * FROM monkeypox_data', (err, results) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json(results);
    });
});

app.get('/monkeypox_data/:id', (req, res) => {
    connection.query('SELECT * FROM monkeypox_data WHERE id=?', [req.params.id], (err, results) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json(results[0] || {});
    });
});

app.post('/monkeypox_data', (req, res) => {
    connection.query('INSERT INTO monkeypox_data SET ?', req.body, (err, results) => {
      if (err) return res.status(500).json({ error: err.message });
      res.status(201).json({ id: results.insertId, ...req.body });
    });
});

app.put('/monkeypox_data/:id', (req, res) => {
    connection.query('UPDATE monkeypox_data SET ? WHERE id=?', [req.body, req.params.id], (err) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json({ message: 'Donnée mise à jour avec succès' });
    });
});

app.delete('/monkeypox_data/:id', (req, res) => {
    connection.query('DELETE FROM monkeypox_data WHERE id=?', [req.params.id], (err) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json({ message: 'Donnée supprimée avec succès' });
    });
});

// CRUD complet pour coronavirus_daily
app.get('/coronavirus_daily', (req, res) => {
    connection.query('SELECT * FROM coronavirus_daily', (err, results) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json(results);
    });
});

app.get('/coronavirus_daily/:id', (req, res) => {
    connection.query('SELECT * FROM coronavirus_daily WHERE id=?', [req.params.id], (err, results) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json(results[0] || {});
    });
});

app.post('/coronavirus_daily', (req, res) => {
    connection.query('INSERT INTO coronavirus_daily SET ?', req.body, (err, results) => {
      if (err) return res.status(500).json({ error: err.message });
      res.status(201).json({ id: results.insertId, ...req.body });
    });
});

app.put('/coronavirus_daily/:id', (req, res) => {
    connection.query('UPDATE coronavirus_daily SET ? WHERE id=?', [req.body, req.params.id], (err) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json({ message: 'Donnée mise à jour avec succès' });
    });
});

app.delete('/coronavirus_daily/:id', (req, res) => {
    connection.query('DELETE FROM coronavirus_daily WHERE id=?', [req.params.id], (err) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json({ message: 'Donnée supprimée avec succès' });
    });
});

app.listen(port, () => {
  console.log(`🚀 API démarrée sur http://localhost:${port}`);
});
