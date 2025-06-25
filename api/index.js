const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');
const swaggerUi = require('swagger-ui-express');
const YAML = require('yamljs');
const bcrypt = require('bcrypt');

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
// app.get('/covid_country', (req, res) => {
//     connection.query('SELECT * FROM covid_country', (err, results) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json(results);
//     });
// });

// app.get('/covid_country/:id', (req, res) => {
//     connection.query('SELECT * FROM covid_country WHERE id=?', [req.params.id], (err, results) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json(results[0] || {});
//     });
// });

// app.post('/covid_country', (req, res) => {
//     const { country_region, confirmed } = req.body;
//     if (!country_region || !confirmed) {
//         return res.status(400).json({ error: "Certains champs obligatoires sont manquants." });
//     }

//     connection.query(
//       'INSERT INTO covid_country SET ?', req.body,
//       (err, results) => {
//         if (err) return res.status(500).json({ error: err.message });
//         res.status(201).json({ id: results.insertId, ...req.body });
//       }
//     );
// });

// app.put('/covid_country/:id', (req, res) => {
//     connection.query('UPDATE covid_country SET ? WHERE id=?', [req.body, req.params.id], (err) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json({ message: 'Donnée mise à jour avec succès' });
//     });
// });

// app.delete('/covid_country/:id', (req, res) => {
//     connection.query('DELETE FROM covid_country WHERE id=?', [req.params.id], (err) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json({ message: 'Donnée supprimée avec succès' });
//     });
// });

// // CRUD complet pour monkeypox_data
// app.get('/monkeypox_data', (req, res) => {
//     connection.query('SELECT * FROM monkeypox_data', (err, results) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json(results);
//     });
// });

// app.get('/monkeypox_data/:id', (req, res) => {
//     connection.query('SELECT * FROM monkeypox_data WHERE id=?', [req.params.id], (err, results) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json(results[0] || {});
//     });
// });

// app.post('/monkeypox_data', (req, res) => {
//     connection.query('INSERT INTO monkeypox_data SET ?', req.body, (err, results) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.status(201).json({ id: results.insertId, ...req.body });
//     });
// });

// app.put('/monkeypox_data/:id', (req, res) => {
//     connection.query('UPDATE monkeypox_data SET ? WHERE id=?', [req.body, req.params.id], (err) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json({ message: 'Donnée mise à jour avec succès' });
//     });
// });

// app.delete('/monkeypox_data/:id', (req, res) => {
//     connection.query('DELETE FROM monkeypox_data WHERE id=?', [req.params.id], (err) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json({ message: 'Donnée supprimée avec succès' });
//     });
// });

// // CRUD complet pour coronavirus_daily
// app.get('/coronavirus_daily', (req, res) => {
//     connection.query('SELECT * FROM coronavirus_daily', (err, results) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json(results);
//     });
// });

// app.get('/coronavirus_daily/:id', (req, res) => {
//     connection.query('SELECT * FROM coronavirus_daily WHERE id=?', [req.params.id], (err, results) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json(results[0] || {});
//     });
// });

// app.post('/coronavirus_daily', (req, res) => {
//     connection.query('INSERT INTO coronavirus_daily SET ?', req.body, (err, results) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.status(201).json({ id: results.insertId, ...req.body });
//     });
// });

// app.put('/coronavirus_daily/:id', (req, res) => {
//     connection.query('UPDATE coronavirus_daily SET ? WHERE id=?', [req.body, req.params.id], (err) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json({ message: 'Donnée mise à jour avec succès' });
//     });
// });

// app.delete('/coronavirus_daily/:id', (req, res) => {
//     connection.query('DELETE FROM coronavirus_daily WHERE id=?', [req.params.id], (err) => {
//       if (err) return res.status(500).json({ error: err.message });
//       res.json({ message: 'Donnée supprimée avec succès' });
//     });
// });




//CRUD pour continent

app.get('/continents', (req, res) => {
    connection.query('SELECT * FROM continent', (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

app.get('/continents/:idContinent', (req, res) => {
    connection.query('SELECT * FROM continent WHERE idContinent=?', [req.params.idContinent], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results[0] || {});
    });
});

app.post('/continents', (req, res) => {
    const { idContinent, continent } = req.body;
    if (!idContinent || !continent) {
        return res.status(400).json({ error: "Les champs 'idContinent' et 'continent' sont obligatoires." });
    }
    connection.query('INSERT INTO continent SET ?', req.body, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.status(201).json({ idContinent: req.body.idContinent, ...req.body });
    });
});

app.put('/continents/:idContinent', (req, res) => {
    connection.query('UPDATE continent SET ? WHERE idContinent=?', [req.body, req.params.idContinent], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Continent mis à jour avec succès' });
    });
});

app.delete('/continents/:idContinent', (req, res) => {
    connection.query('DELETE FROM continent WHERE idContinent=?', [req.params.idContinent], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Continent supprimé avec succès' });
    });
});

// CRUD pour pays

app.get('/pays', (req, res) => {
    connection.query('SELECT p.*, c.continent FROM pays p JOIN continent c ON p.idContinent = c.idContinent', (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

app.get('/pays/:id_pays', (req, res) => {
    connection.query('SELECT p.*, c.continent FROM pays p JOIN continent c ON p.idContinent = c.idContinent WHERE p.id_pays=?', [req.params.id_pays], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results[0] || {});
    });
});

app.post('/pays', (req, res) => {
    const { id_pays, pays, idContinent } = req.body;
    if (!id_pays || !pays || !idContinent) {
        return res.status(400).json({ error: "Les champs 'id_pays', 'pays' et 'idContinent' sont obligatoires." });
    }
    connection.query('INSERT INTO pays SET ?', req.body, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.status(201).json({ id_pays: req.body.id_pays, ...req.body });
    });
});

app.put('/pays/:id_pays', (req, res) => {
    connection.query('UPDATE pays SET ? WHERE id_pays=?', [req.body, req.params.id_pays], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Pays mis à jour avec succès' });
    });
});

app.delete('/pays/:id_pays', (req, res) => {
    connection.query('DELETE FROM pays WHERE id_pays=?', [req.params.id_pays], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Pays supprimé avec succès' });
    });
});

// CRUD pour covid_country (avec jointures)

app.get('/covid_country', (req, res) => {
    const query = `
        SELECT cc.*, p.pays, c.continent
        FROM covid_country cc
        JOIN pays p ON cc.id_pays = p.id_pays
        JOIN continent c ON cc.idContinent = c.idContinent
    `;
    connection.query(query, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

app.get('/covid_country/:id', (req, res) => {
    const query = `
        SELECT cc.*, p.pays, c.continent
        FROM covid_country cc
        JOIN pays p ON cc.id_pays = p.id_pays
        JOIN continent c ON cc.idContinent = c.idContinent
        WHERE cc.id=?
    `;
    connection.query(query, [req.params.id], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results[0] || {});
    });
});

app.post('/covid_country', (req, res) => {  
    const { nbCas, nbMort, nbSoigne, id_pays, idContinent } = req.body;
    if (!nbCas || !nbMort || !nbSoigne || !id_pays || !idContinent) {
        return res.status(400).json({ error: "Certains champs obligatoires sont manquants (nbCas, nbMort, nbSoigne, id_pays, idContinent)." });
    }

    connection.query('INSERT INTO covid_country SET ?', req.body, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.status(201).json({ id: results.insertId, ...req.body });
    });
});

app.put('/covid_country/:id', (req, res) => {
    connection.query('UPDATE covid_country SET ? WHERE id=?', [req.body, req.params.id], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Donnée COVID par pays mise à jour avec succès' });
    });
});

app.delete('/covid_country/:id', (req, res) => {
    connection.query('DELETE FROM covid_country WHERE id=?', [req.params.id], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Donnée COVID par pays supprimée avec succès' });
    });
});

// CRUD pour monkeypox_data (avec jointures)


app.get('/monkeypox_data', (req, res) => {
    const query = `
        SELECT md.*, p.pays, c.continent
        FROM monkeypox_data md
        JOIN pays p ON md.id_pays = p.id_pays
        JOIN continent c ON md.idContinent = c.idContinent
    `;
    connection.query(query, (err, results) => {
        if (err) {
            console.error("Erreur lors de la récupération des données monkeypox_data :", err.message);
            return res.status(500).json({ error: "Erreur serveur lors de la récupération des données Monkeypox." });
        }
        res.json(results);
    });
});

app.get('/monkeypox_data/:id', (req, res) => {
    const query = `
        SELECT md.*, p.pays, c.continent
        FROM monkeypox_data md
        JOIN pays p ON md.id_pays = p.id_pays
        JOIN continent c ON md.idContinent = c.idContinent
        WHERE md.id=?
    `;
    connection.query(query, [req.params.id], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results[0] || {});
    });
});

app.post('/monkeypox_data', (req, res) => {
    const { date, nbCasTotaux, nbMortTotaux, id_pays, idContinent } = req.body;
    if (!date || !nbCasTotaux || !nbMortTotaux || !id_pays || !idContinent) {
        return res.status(400).json({ error: "Certains champs obligatoires sont manquants (date, nbCasTotaux, nbMortTotaux, id_pays, idContinent)." });
    }
    connection.query('INSERT INTO monkeypox_data SET ?', req.body, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.status(201).json({ id: results.insertId, ...req.body });
    });
});

app.put('/monkeypox_data/:id', (req, res) => {
    connection.query('UPDATE monkeypox_data SET ? WHERE id=?', [req.body, req.params.id], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Donnée Monkeypox mise à jour avec succès' });
    });
});

app.delete('/monkeypox_data/:id', (req, res) => {
    connection.query('DELETE FROM monkeypox_data WHERE id=?', [req.params.id], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Donnée Monkeypox supprimée avec succès' });
    });
});

// CRUD pour coronavirus_daily (avec jointures)

app.get('/coronavirus_daily', (req, res) => {
    const query = `
        SELECT cd.*, p.pays, c.continent
        FROM coronavirus_daily cd
        JOIN pays p ON cd.id_pays = p.id_pays
        JOIN continent c ON cd.idContinent = c.idContinent
    `;
    connection.query(query, (err, results) => {
        if (err) {
            console.error("Erreur lors de la récupération des données coronavirus_daily :", err.message);
            return res.status(500).json({ error: "Erreur serveur lors de la récupération des données Coronavirus journalières." });
        }
        res.json(results);
    });
});

app.get('/coronavirus_daily/:id', (req, res) => {
    const query = `
        SELECT cd.*, p.pays, c.continent
        FROM coronavirus_daily cd
        JOIN pays p ON cd.id_pays = p.id_pays
        JOIN continent c ON cd.idContinent = c.idContinent
        WHERE cd.id=?
    `;
    connection.query(query, [req.params.id], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results[0] || {});
    });
});

app.post('/coronavirus_daily', (req, res) => {
    const { date, cumulCasTotaux, nouveauCasJournalier, id_pays, idContinent } = req.body;
    if (!date || !cumulCasTotaux || !nouveauCasJournalier || !id_pays || !idContinent) {
        return res.status(400).json({ error: "Certains champs obligatoires sont manquants (date, cumulCasTotaux, nouveauCasJournalier, id_pays, idContinent)." });
    }
    connection.query('INSERT INTO coronavirus_daily SET ?', req.body, (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.status(201).json({ id: results.insertId, ...req.body });
    });
});

app.put('/coronavirus_daily/:id', (req, res) => {
    connection.query('UPDATE coronavirus_daily SET ? WHERE id=?', [req.body, req.params.id], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Donnée Coronavirus journalière mise à jour avec succès' });
    });
});

app.delete('/coronavirus_daily/:id', (req, res) => {
    connection.query('DELETE FROM coronavirus_daily WHERE id=?', [req.params.id], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Donnée Coronavirus journalière supprimée avec succès' });
    });
});

// Récupérer tous les utilisateurs
app.get('/users', (req, res) => {
    connection.query('SELECT * FROM users', (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results);
    });
});

// Récupérer un utilisateur par ID
app.get('/users/:id', (req, res) => {
    connection.query('SELECT * FROM users WHERE id = ?', [req.params.id], (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(results[0] || {});
    });
});

// Ajouter un nouvel utilisateur
app.post('/users', async (req, res) => {
    const { login, mdp, rôle } = req.body;

    if (!login || !mdp || !rôle) {
        return res.status(400).json({ error: "Les champs 'login', 'mdp' et 'rôle' sont obligatoires." });
    }

    try {
        // Hachage du mot de passe
        const hashedPassword = await bcrypt.hash(mdp, 10); // 10 = nombre de "salt rounds"

        const newUser = {
            login,
            mdp: hashedPassword,
            rôle
        };

        connection.query('INSERT INTO users SET ?', newUser, (err, results) => {
            if (err) return res.status(500).json({ error: err.message });
            res.status(201).json({ id: results.insertId, login, rôle });
        });

    } catch (error) {
        res.status(500).json({ error: 'Erreur lors du hachage du mot de passe' });
    }
});

// Mettre à jour un utilisateur existant
app.put('/users/:id', async (req, res) => {
    const { login, mdp, rôle } = req.body;

    if (!login || !mdp || !rôle) {
        return res.status(400).json({ error: "Les champs 'login', 'mdp' et 'rôle' sont obligatoires." });
    }

    try {
        const hashedPassword = await bcrypt.hash(mdp, 10);

        const updatedUser = {
            login,
            mdp: hashedPassword,
            rôle
        };

        connection.query('UPDATE users SET ? WHERE id = ?', [updatedUser, req.params.id], (err) => {
            if (err) return res.status(500).json({ error: err.message });
            res.json({ message: 'Utilisateur mis à jour avec succès' });
        });

    } catch (error) {
        res.status(500).json({ error: 'Erreur lors du hachage du mot de passe' });
    }
});

// Supprimer un utilisateur
app.delete('/users/:id', (req, res) => {
    connection.query('DELETE FROM users WHERE id = ?', [req.params.id], (err) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ message: 'Utilisateur supprimé avec succès' });
    });
});

// Connexion d'un utilisateur
app.post('/users/login', (req, res) => {
    const { login, mdp } = req.body;

    if (!login || !mdp) {
        return res.status(400).json({ error: "Login et mot de passe requis." });
    }

    connection.query('SELECT * FROM users WHERE login = ?', [login], async (err, results) => {
        if (err) return res.status(500).json({ error: err.message });
        if (results.length === 0) return res.status(401).json({ error: "Identifiants incorrects." });

        const user = results[0];
        const isMatch = await bcrypt.compare(mdp, user.mdp);

        if (!isMatch) {
            return res.status(401).json({ error: "Identifiants incorrects." });
        }

        // Envoi des infos utiles (ne pas envoyer le mot de passe)
        res.json({
            id: user.id,
            login: user.login,
            rôle: user.rôle
        });
    });
});



app.listen(port, () => {
  console.log(`🚀 API démarrée sur http://localhost:${port}`);
});
