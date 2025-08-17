require("dotenv").config();

const express = require("express");
const app = express();
const YAML = require("yamljs");
const swaggerUi = require("swagger-ui-express");
const swaggerDocument = YAML.load("./swagger.yaml");
const verifyToken = require("./middleware/verifyToken");

// const pays = process.env.PAYS_CIBLE || 'FR';
// console.log("🌍 Environnement pays :", pays);

const pays = process.env.PAYS_CIBLE;
console.log("🌍 Environnement pays :", pays ?? "aucun");
console.log("🌍 pays brut =", pays, " typeof=", typeof pays);

// const mysql = require('mysql2');
const mysql = require("mysql2/promise");
const dbHost = process.env.DB_HOST || `localhost`;

// const connection = mysql.createConnection({
//     host: 'db',
//     user: 'root',
//     password: 'rootpassword',
//     database: 'bdd_mspr_api',
//     port: 3306
//   });

// const connection = mysql.createConnection({
//     host: process.env.DB_HOST || 'db',
//     user: process.env.DB_USER || 'root',
//     password: process.env.DB_PASSWORD || 'rootpassword',
//     database: process.env.DB_NAME || 'bdd_mspr_api',
//     port: process.env.DB_PORT || 3306,
//     waitForConnections: true,
//     connectionLimit: 10,
//     queueLimit: 0
// });

// ancien
// const connection = mysql.createConnection({...})

const connection = mysql.createPool({
  host: process.env.DB_HOST || "db",
  user: process.env.DB_USER || "root",
  password: process.env.DB_PASSWORD || "rootpassword",
  database: process.env.DB_NAME || "bdd_mspr_api",
  port: process.env.DB_PORT || 3306,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

// const connectWithRetry = () => {
//   connection.connect((err) => {
//     if (err) {
//       console.error('❌ Erreur de connexion à MySQL:', err.message);
//       console.log('🔄 Nouvelle tentative de connexion dans 5 secondes...');
//       setTimeout(connectWithRetry, 5000);
//     } else {
//       console.log('✅ Connecté à la base de données MySQL');
//     }
//   });
// };

// connectWithRetry();

app.use(express.json());


if (pays === "FR" || pays === "CH") {
  const allowedPaths = ["/users", "/users/{id}", "/users/login"];
  for (const path in swaggerDocument.paths) {
    if (!allowedPaths.includes(path)) {
      delete swaggerDocument.paths[path];
    }
  }
  console.log(
    "🚫 Swagger : seules les routes utilisateurs sont documentées pour ce pays"
  );
}

app.use("/api-docs", swaggerUi.serve, swaggerUi.setup(swaggerDocument));

const cors = require("cors");

const bcrypt = require("bcrypt");

const port = process.env.PORT || 3001;

app.use(cors());
app.use(express.json());

app.use((req, res, next) => {
  res.header("Access-Control-Allow-Origin", "*");
  res.header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
  res.header(
    "Access-Control-Allow-Headers",
    "Origin, X-Requested-With, Content-Type, Accept"
  );
  next();
});

app.options("*", (req, res) => res.sendStatus(200));

app.use("/api-docs", swaggerUi.serve, swaggerUi.setup(swaggerDocument));

app.get("/", (req, res) => {
  res.send("API COVID-19 Node.js avec MYSQL");
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

// Récupération du pays
// const pays = process.env.PAYS_CIBLE || 'FR';
console.log("🌍 Environnement pays :", pays);

// -------------------------------------------------
// ROUTES A ACTIVER UNIQUEMENT POUR US
// -------------------------------------------------
if (
  pays === "US" ||
  pays === undefined ||
  pays === "" ||
  pays === "undefined"
) {
  //CRUD pour continent

  app.get("/continents", async (req, res) => {
    try {
      const [results] = await connection.query("SELECT * FROM continent");
      res.json(results);
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  app.get("/continents/:idContinent", async (req, res) => {
    try {
      const [results] = await connection.query(
        "SELECT * FROM continent WHERE idContinent=?",
        [req.params.idContinent]
      );
      res.json(results[0] || {});
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  //   app.post("/continents", (req, res) => {
  //     const { idContinent, continent } = req.body;
  //     if (!idContinent || !continent) {
  //       return res
  //         .status(400)
  //         .json({
  //           error: "Les champs 'idContinent' et 'continent' sont obligatoires.",
  //         });
  //     }
  //     connection.query(
  //       "INSERT INTO continent SET ?",
  //       req.body,
  //       (err, results) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res
  //           .status(201)
  //           .json({ idContinent: req.body.idContinent, ...req.body });
  //       }
  //     );
  //   });

  app.post("/continents", async (req, res) => {
    const { idContinent, continent } = req.body;
    if (!idContinent || !continent) {
      return res.status(400).json({ error: "Champs obligatoires manquants" });
    }
    try {
      await connection.query("INSERT INTO continent SET ?", req.body);
      res.status(201).json(req.body);
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  //   app.put("/continents/:idContinent", (req, res) => {
  //     connection.query(
  //       "UPDATE continent SET ? WHERE idContinent=?",
  //       [req.body, req.params.idContinent],
  //       (err) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json({ message: "Continent mis à jour avec succès" });
  //       }
  //     );
  //   });

  app.put("/continents/:idContinent", async (req, res) => {
    try {
      await connection.query("UPDATE continent SET ? WHERE idContinent=?", [
        req.body,
        req.params.idContinent,
      ]);
      res.json({ message: "Continent mis à jour avec succès" });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  //   app.delete("/continents/:idContinent", (req, res) => {
  //     connection.query(
  //       "DELETE FROM continent WHERE idContinent=?",
  //       [req.params.idContinent],
  //       (err) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json({ message: "Continent supprimé avec succès" });
  //       }
  //     );
  //   });

  app.delete("/continents/:idContinent", async (req, res) => {
    try {
      await connection.query("DELETE FROM continent WHERE idContinent=?", [
        req.params.idContinent,
      ]);
      res.json({ message: "Continent supprimé avec succès" });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // CRUD pour pays

  //   app.get("/pays", (req, res) => {
  //     connection.query(
  //       "SELECT p.*, c.continent FROM pays p JOIN continent c ON p.idContinent = c.idContinent",
  //       (err, results) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json(results);
  //       }
  //     );
  //   });

  //   app.get("/pays/:id_pays", (req, res) => {
  //     connection.query(
  //       "SELECT p.*, c.continent FROM pays p JOIN continent c ON p.idContinent = c.idContinent WHERE p.id_pays=?",
  //       [req.params.id_pays],
  //       (err, results) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json(results[0] || {});
  //       }
  //     );
  //   });

  //   app.post("/pays", (req, res) => {
  //     const { id_pays, pays, idContinent } = req.body;
  //     if (!id_pays || !pays || !idContinent) {
  //       return res
  //         .status(400)
  //         .json({
  //           error:
  //             "Les champs 'id_pays', 'pays' et 'idContinent' sont obligatoires.",
  //         });
  //     }
  //     connection.query("INSERT INTO pays SET ?", req.body, (err, results) => {
  //       if (err) return res.status(500).json({ error: err.message });
  //       res.status(201).json({ id_pays: req.body.id_pays, ...req.body });
  //     });
  //   });

  //   app.put("/pays/:id_pays", (req, res) => {
  //     connection.query(
  //       "UPDATE pays SET ? WHERE id_pays=?",
  //       [req.body, req.params.id_pays],
  //       (err) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json({ message: "Pays mis à jour avec succès" });
  //       }
  //     );
  //   });

  //   app.delete("/pays/:id_pays", (req, res) => {
  //     connection.query(
  //       "DELETE FROM pays WHERE id_pays=?",
  //       [req.params.id_pays],
  //       (err) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json({ message: "Pays supprimé avec succès" });
  //       }
  //     );
  //   });

  app.get("/pays", async (req, res) => {
    try {
      const [results] = await connection.query(
        `SELECT p.*, c.continent FROM pays p JOIN continent c ON p.idContinent = c.idContinent`
      );
      res.json(results);
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  app.get("/pays/:id_pays", async (req, res) => {
    try {
      const [results] = await connection.query(
        `SELECT p.*, c.continent FROM pays p JOIN continent c ON p.idContinent = c.idContinent WHERE p.id_pays=?`,
        [req.params.id_pays]
      );
      res.json(results[0] || {});
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  app.post("/pays", async (req, res) => {
    const { id_pays, pays, idContinent } = req.body;
    if (!id_pays || !pays || !idContinent) {
      return res.status(400).json({ error: "Champs obligatoires manquants" });
    }
    try {
      await connection.query("INSERT INTO pays SET ?", req.body);
      res.status(201).json(req.body);
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  app.put("/pays/:id_pays", async (req, res) => {
    try {
      await connection.query("UPDATE pays SET ? WHERE id_pays=?", [
        req.body,
        req.params.id_pays,
      ]);
      res.json({ message: "Pays mis à jour avec succès" });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  app.delete("/pays/:id_pays", async (req, res) => {
    try {
      await connection.query("DELETE FROM pays WHERE id_pays=?", [
        req.params.id_pays,
      ]);
      res.json({ message: "Pays supprimé avec succès" });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // CRUD pour covid_country (avec jointures)

  //   app.get("/covid_country", (req, res) => {
  //     const query = `
  //         SELECT cc.*, p.pays, c.continent
  //         FROM covid_country cc
  //         JOIN pays p ON cc.id_pays = p.id_pays
  //         JOIN continent c ON cc.idContinent = c.idContinent
  //     `;
  //     connection.query(query, (err, results) => {
  //       if (err) return res.status(500).json({ error: err.message });
  //       res.json(results);
  //     });
  //   });

  //   app.get("/covid_country/:id", (req, res) => {
  //     const query = `
  //         SELECT cc.*, p.pays, c.continent
  //         FROM covid_country cc
  //         JOIN pays p ON cc.id_pays = p.id_pays
  //         JOIN continent c ON cc.idContinent = c.idContinent
  //         WHERE cc.id=?
  //     `;
  //     connection.query(query, [req.params.id], (err, results) => {
  //       if (err) return res.status(500).json({ error: err.message });
  //       res.json(results[0] || {});
  //     });
  //   });

  //   app.post("/covid_country", (req, res) => {
  //     const { nbCas, nbMort, nbSoigne, id_pays, idContinent } = req.body;
  //     if (!nbCas || !nbMort || !nbSoigne || !id_pays || !idContinent) {
  //       return res
  //         .status(400)
  //         .json({
  //           error:
  //             "Certains champs obligatoires sont manquants (nbCas, nbMort, nbSoigne, id_pays, idContinent).",
  //         });
  //     }

  //     connection.query(
  //       "INSERT INTO covid_country SET ?",
  //       req.body,
  //       (err, results) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.status(201).json({ id: results.insertId, ...req.body });
  //       }
  //     );
  //   });

  //   app.put("/covid_country/:id", (req, res) => {
  //     connection.query(
  //       "UPDATE covid_country SET ? WHERE id=?",
  //       [req.body, req.params.id],
  //       (err) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json({ message: "Donnée COVID par pays mise à jour avec succès" });
  //       }
  //     );
  //   });

  //   app.delete("/covid_country/:id", (req, res) => {
  //     connection.query(
  //       "DELETE FROM covid_country WHERE id=?",
  //       [req.params.id],
  //       (err) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json({ message: "Donnée COVID par pays supprimée avec succès" });
  //       }
  //     );
  //   });

  app.get("/covid_country", async (req, res) => {
    try {
      const [results] = await connection.query(`
      SELECT cc.*, p.pays, c.continent
      FROM covid_country cc
      JOIN pays p ON cc.id_pays = p.id_pays
      JOIN continent c ON cc.idContinent = c.idContinent
    `);
      res.json(results);
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // GET by ID
  app.get("/covid_country/:id", async (req, res) => {
    try {
      const [results] = await connection.query(
        `
      SELECT cc.*, p.pays, c.continent
      FROM covid_country cc
      JOIN pays p ON cc.id_pays = p.id_pays
      JOIN continent c ON cc.idContinent = c.idContinent
      WHERE cc.id = ?
    `,
        [req.params.id]
      );
      res.json(results[0] || {});
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // POST
  app.post("/covid_country", async (req, res) => {
    const { nbCas, nbMort, nbSoigne, id_pays, idContinent } = req.body;
    if (!nbCas || !nbMort || !nbSoigne || !id_pays || !idContinent) {
      return res.status(400).json({ error: "Champs obligatoires manquants." });
    }
    try {
      const [result] = await connection.query(
        "INSERT INTO covid_country SET ?",
        req.body
      );
      res.status(201).json({ id: result.insertId, ...req.body });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // PUT
  app.put("/covid_country/:id", async (req, res) => {
    try {
      await connection.query("UPDATE covid_country SET ? WHERE id = ?", [
        req.body,
        req.params.id,
      ]);
      res.json({ message: "Covid_country mis à jour." });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // DELETE
  app.delete("/covid_country/:id", async (req, res) => {
    try {
      await connection.query("DELETE FROM covid_country WHERE id = ?", [
        req.params.id,
      ]);
      res.json({ message: "Covid_country supprimé." });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // CRUD pour monkeypox_data (avec jointures)

  //   app.get("/monkeypox_data", (req, res) => {
  //     const query = `
  //         SELECT md.*, p.pays, c.continent
  //         FROM monkeypox_data md
  //         JOIN pays p ON md.id_pays = p.id_pays
  //         JOIN continent c ON md.idContinent = c.idContinent
  //     `;
  //     connection.query(query, (err, results) => {
  //       if (err) {
  //         console.error(
  //           "Erreur lors de la récupération des données monkeypox_data :",
  //           err.message
  //         );
  //         return res
  //           .status(500)
  //           .json({
  //             error:
  //               "Erreur serveur lors de la récupération des données Monkeypox.",
  //           });
  //       }
  //       res.json(results);
  //     });
  //   });

  //   app.get("/monkeypox_data/:id", (req, res) => {
  //     const query = `
  //         SELECT md.*, p.pays, c.continent
  //         FROM monkeypox_data md
  //         JOIN pays p ON md.id_pays = p.id_pays
  //         JOIN continent c ON md.idContinent = c.idContinent
  //         WHERE md.id=?
  //     `;
  //     connection.query(query, [req.params.id], (err, results) => {
  //       if (err) return res.status(500).json({ error: err.message });
  //       res.json(results[0] || {});
  //     });
  //   });

  //   app.post("/monkeypox_data", (req, res) => {
  //     const { date, nbCasTotaux, nbMortTotaux, id_pays, idContinent } = req.body;
  //     if (!date || !nbCasTotaux || !nbMortTotaux || !id_pays || !idContinent) {
  //       return res
  //         .status(400)
  //         .json({
  //           error:
  //             "Certains champs obligatoires sont manquants (date, nbCasTotaux, nbMortTotaux, id_pays, idContinent).",
  //         });
  //     }
  //     connection.query(
  //       "INSERT INTO monkeypox_data SET ?",
  //       req.body,
  //       (err, results) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.status(201).json({ id: results.insertId, ...req.body });
  //       }
  //     );
  //   });

  //   app.put("/monkeypox_data/:id", (req, res) => {
  //     connection.query(
  //       "UPDATE monkeypox_data SET ? WHERE id=?",
  //       [req.body, req.params.id],
  //       (err) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json({ message: "Donnée Monkeypox mise à jour avec succès" });
  //       }
  //     );
  //   });

  //   app.delete("/monkeypox_data/:id", (req, res) => {
  //     connection.query(
  //       "DELETE FROM monkeypox_data WHERE id=?",
  //       [req.params.id],
  //       (err) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json({ message: "Donnée Monkeypox supprimée avec succès" });
  //       }
  //     );
  //   });

  // GET all
  app.get("/monkeypox_data", async (req, res) => {
    try {
      const [results] = await connection.query(`
      SELECT md.*, p.pays, c.continent
      FROM monkeypox_data md
      JOIN pays p ON md.id_pays = p.id_pays
      JOIN continent c ON md.idContinent = c.idContinent
    `);
      res.json(results);
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // GET by ID
  app.get("/monkeypox_data/:id", async (req, res) => {
    try {
      const [results] = await connection.query(
        `
      SELECT md.*, p.pays, c.continent
      FROM monkeypox_data md
      JOIN pays p ON md.id_pays = p.id_pays
      JOIN continent c ON md.idContinent = c.idContinent
      WHERE md.id = ?
    `,
        [req.params.id]
      );
      res.json(results[0] || {});
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // POST
  app.post("/monkeypox_data", async (req, res) => {
    const { date, nbCasTotaux, nbMortTotaux, id_pays, idContinent } = req.body;
    if (!date || !nbCasTotaux || !nbMortTotaux || !id_pays || !idContinent) {
      return res.status(400).json({ error: "Champs obligatoires manquants." });
    }
    try {
      const [result] = await connection.query(
        "INSERT INTO monkeypox_data SET ?",
        req.body
      );
      res.status(201).json({ id: result.insertId, ...req.body });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // PUT
  app.put("/monkeypox_data/:id", async (req, res) => {
    try {
      await connection.query("UPDATE monkeypox_data SET ? WHERE id = ?", [
        req.body,
        req.params.id,
      ]);
      res.json({ message: "Monkeypox_data mis à jour." });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // DELETE
  app.delete("/monkeypox_data/:id", async (req, res) => {
    try {
      await connection.query("DELETE FROM monkeypox_data WHERE id = ?", [
        req.params.id,
      ]);
      res.json({ message: "Monkeypox_data supprimé." });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // CRUD pour coronavirus_daily (avec jointures)

  //   app.get("/coronavirus_daily", (req, res) => {
  //     const query = `
  //         SELECT cd.*, p.pays, c.continent
  //         FROM coronavirus_daily cd
  //         JOIN pays p ON cd.id_pays = p.id_pays
  //         JOIN continent c ON cd.idContinent = c.idContinent
  //     `;
  //     connection.query(query, (err, results) => {
  //       if (err) {
  //         console.error(
  //           "Erreur lors de la récupération des données coronavirus_daily :",
  //           err.message
  //         );
  //         return res
  //           .status(500)
  //           .json({
  //             error:
  //               "Erreur serveur lors de la récupération des données Coronavirus journalières.",
  //           });
  //       }
  //       res.json(results);
  //     });
  //   });

  //   app.get("/coronavirus_daily/:id", (req, res) => {
  //     const query = `
  //         SELECT cd.*, p.pays, c.continent
  //         FROM coronavirus_daily cd
  //         JOIN pays p ON cd.id_pays = p.id_pays
  //         JOIN continent c ON cd.idContinent = c.idContinent
  //         WHERE cd.id=?
  //     `;
  //     connection.query(query, [req.params.id], (err, results) => {
  //       if (err) return res.status(500).json({ error: err.message });
  //       res.json(results[0] || {});
  //     });
  //   });

  //   app.post("/coronavirus_daily", (req, res) => {
  //     const { date, cumulCasTotaux, nouveauCasJournalier, id_pays, idContinent } =
  //       req.body;
  //     if (
  //       !date ||
  //       !cumulCasTotaux ||
  //       !nouveauCasJournalier ||
  //       !id_pays ||
  //       !idContinent
  //     ) {
  //       return res
  //         .status(400)
  //         .json({
  //           error:
  //             "Certains champs obligatoires sont manquants (date, cumulCasTotaux, nouveauCasJournalier, id_pays, idContinent).",
  //         });
  //     }
  //     connection.query(
  //       "INSERT INTO coronavirus_daily SET ?",
  //       req.body,
  //       (err, results) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.status(201).json({ id: results.insertId, ...req.body });
  //       }
  //     );
  //   });

  //   app.put("/coronavirus_daily/:id", (req, res) => {
  //     connection.query(
  //       "UPDATE coronavirus_daily SET ? WHERE id=?",
  //       [req.body, req.params.id],
  //       (err) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json({
  //           message: "Donnée Coronavirus journalière mise à jour avec succès",
  //         });
  //       }
  //     );
  //   });

  //   app.delete("/coronavirus_daily/:id", (req, res) => {
  //     connection.query(
  //       "DELETE FROM coronavirus_daily WHERE id=?",
  //       [req.params.id],
  //       (err) => {
  //         if (err) return res.status(500).json({ error: err.message });
  //         res.json({
  //           message: "Donnée Coronavirus journalière supprimée avec succès",
  //         });
  //       }
  //     );
  //   });

  // GET all
  app.get("/coronavirus_daily", async (req, res) => {
    try {
      const [results] = await connection.query(`
      SELECT cd.*, p.pays, c.continent
      FROM coronavirus_daily cd
      JOIN pays p ON cd.id_pays = p.id_pays
      JOIN continent c ON cd.idContinent = c.idContinent
    `);
      res.json(results);
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // GET by ID
  app.get("/coronavirus_daily/:id", async (req, res) => {
    try {
      const [results] = await connection.query(
        `
      SELECT cd.*, p.pays, c.continent
      FROM coronavirus_daily cd
      JOIN pays p ON cd.id_pays = p.id_pays
      JOIN continent c ON cd.idContinent = c.idContinent
      WHERE cd.id = ?
    `,
        [req.params.id]
      );
      res.json(results[0] || {});
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // POST
  app.post("/coronavirus_daily", async (req, res) => {
    const { date, cumulCasTotaux, nouveauCasJournalier, id_pays, idContinent } =
      req.body;
    if (
      !date ||
      !cumulCasTotaux ||
      !nouveauCasJournalier ||
      !id_pays ||
      !idContinent
    ) {
      return res.status(400).json({ error: "Champs obligatoires manquants." });
    }
    try {
      const [result] = await connection.query(
        "INSERT INTO coronavirus_daily SET ?",
        req.body
      );
      res.status(201).json({ id: result.insertId, ...req.body });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // PUT
  app.put("/coronavirus_daily/:id", async (req, res) => {
    try {
      await connection.query("UPDATE coronavirus_daily SET ? WHERE id = ?", [
        req.body,
        req.params.id,
      ]);
      res.json({ message: "Coronavirus_daily mis à jour." });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  // DELETE
  app.delete("/coronavirus_daily/:id", async (req, res) => {
    try {
      await connection.query("DELETE FROM coronavirus_daily WHERE id = ?", [
        req.params.id,
      ]);
      res.json({ message: "Coronavirus_daily supprimé." });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  });

  console.log("✅ Toutes les routes activées pour les USA");
} else {
  console.log(
    "🚫 En France et Suisse : seules les routes utilisateurs sont actives"
  );
}

// -------------------------------------------------
// ROUTES /USERS (toujours actives)
// -------------------------------------------------

// Récupérer tous les utilisateurs
// app.get('/users', verifyToken, (req, res) => {
//     connection.query('SELECT * FROM users', (err, results) => {
//         if (err) return res.status(500).json({ error: err.message });
//         res.json(results);
//     });
// });

app.get("/users", verifyToken, async (req, res) => {
  try {
    const [results] = await connection.query("SELECT * FROM users");
    res.json(results);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// // Récupérer un utilisateur par ID
// app.get('/users', verifyToken, (req, res) => {
//     connection.query('SELECT * FROM users', (err, results) => {
//         if (err) {
//             console.error("❌ ERREUR /users :", err.message);  // <==== ajoute ceci
//             return res.status(500).json({ error: err.message });
//         }
//         res.json(results);
//     });
// });

// // Ajouter un nouvel utilisateur
// app.post('/users', async (req, res) => {
//     const { login, mdp, rôle } = req.body;

//     if (!login || !mdp || !rôle) {
//         return res.status(400).json({ error: "Les champs 'login', 'mdp' et 'rôle' sont obligatoires." });
//     }

//     try {
//         const hashedPassword = await bcrypt.hash(mdp, 10);

//         const newUser = { login, mdp: hashedPassword, rôle };

//         connection.query('INSERT INTO users SET ?', newUser, (err, results) => {
//             if (err) {
//                 console.error("❌ ERREUR POST /users :", err.message); // <==== ajoute ceci
//                 return res.status(500).json({ error: err.message });
//             }
//             res.status(201).json({ id: results.insertId, login, rôle });
//         });
//     } catch (error) {
//         console.error("❌ ERREUR POST /users (bcrypt) :", error.message); // <==== ajoute ceci
//         res.status(500).json({ error: 'Erreur lors du hachage du mot de passe' });
//     }
// });

app.post("/users", async (req, res) => {
  const { login, mdp, rôle } = req.body;

  if (!login || !mdp || !rôle) {
    return res.status(400).json({
      error: "Les champs 'login', 'mdp' et 'rôle' sont obligatoires.",
    });
  }

  try {
    const hashedPassword = await bcrypt.hash(mdp, 10);
    const newUser = { login, mdp: hashedPassword, rôle };
    const [results] = await connection.query(
      "INSERT INTO users SET ?",
      newUser
    );
    res.status(201).json({ id: results.insertId, login, rôle });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// // Mettre à jour un utilisateur existant
// app.put('/users/:id', verifyToken, async (req, res) => {
//     const { login, mdp, rôle } = req.body;

//     if (!login || !mdp || !rôle) {
//         return res.status(400).json({ error: "Les champs 'login', 'mdp' et 'rôle' sont obligatoires." });
//     }

//     try {
//         const hashedPassword = await bcrypt.hash(mdp, 10);

//         const updatedUser = {
//             login,
//             mdp: hashedPassword,
//             rôle
//         };

//         connection.query('UPDATE users SET ? WHERE id = ?', [updatedUser, req.params.id], (err) => {
//             if (err) return res.status(500).json({ error: err.message });
//             res.json({ message: 'Utilisateur mis à jour avec succès' });
//         });

//     } catch (error) {
//         res.status(500).json({ error: 'Erreur lors du hachage du mot de passe' });
//     }
// });

app.put("/users/:id", verifyToken, async (req, res) => {
  const { login, mdp, rôle } = req.body;

  if (!login || !mdp || !rôle) {
    return res.status(400).json({
      error: "Les champs 'login', 'mdp' et 'rôle' sont obligatoires.",
    });
  }

  try {
    const hashedPassword = await bcrypt.hash(mdp, 10);
    const updatedUser = { login, mdp: hashedPassword, rôle };
    await connection.query("UPDATE users SET ? WHERE id = ?", [
      updatedUser,
      req.params.id,
    ]);
    res.json({ message: "Utilisateur mis à jour avec succès" });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// // Supprimer un utilisateur
// app.delete('/users/:id', verifyToken, (req, res) => {
//     connection.query('DELETE FROM users WHERE id = ?', [req.params.id], (err) => {
//         if (err) return res.status(500).json({ error: err.message });
//         res.json({ message: 'Utilisateur supprimé avec succès' });
//     });
// });

app.delete("/users/:id", verifyToken, async (req, res) => {
  try {
    await connection.query("DELETE FROM users WHERE id = ?", [req.params.id]);
    res.json({ message: "Utilisateur supprimé avec succès" });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// // Connexion d'un utilisateur
// app.post('/users/login', (req, res) => {
//     const { login, mdp } = req.body;

//     if (!login || !mdp) {
//         return res.status(400).json({ error: "Login et mot de passe requis." });
//     }

//     connection.query('SELECT * FROM users WHERE login = ?', [login], async (err, results) => {
//         if (err) return res.status(500).json({ error: err.message });
//         if (results.length === 0) return res.status(401).json({ error: "Identifiants incorrects." });

//         const user = results[0];
//         const isMatch = await bcrypt.compare(mdp, user.mdp);

//         if (!isMatch) {
//             return res.status(401).json({ error: "Identifiants incorrects." });
//         }

//         // JWT
//         const jwt = require(`jsonwebtoken`);
//         const token = jwt.sign(
//             { id: user.id, login: user.login, rôle: user.rôle },
//             process.env.JWT_SECRET || 'mon_secret_super_dur',
//             { expiresIn: `2h`}
//         );

//         // Envoi des infos utiles (ne pas envoyer le mot de passe)
//         res.json({
//             id: user.id,
//             login: user.login,
//             rôle: user.rôle,
//             token
//         });
//     });
// });

app.post("/users/login", async (req, res) => {
  const { login, mdp } = req.body;

  if (!login || !mdp) {
    return res.status(400).json({ error: "Login et mot de passe requis." });
  }

  try {
    const [results] = await connection.query(
      "SELECT * FROM users WHERE login = ?",
      [login]
    );
    if (results.length === 0) {
      return res.status(401).json({ error: "Identifiants incorrects." });
    }

    const user = results[0];
    const isMatch = await bcrypt.compare(mdp, user.mdp);

    if (!isMatch) {
      return res.status(401).json({ error: "Identifiants incorrects." });
    }

    const jwt = require(`jsonwebtoken`);
    const token = jwt.sign(
      { id: user.id, login: user.login, rôle: user.rôle },
      process.env.JWT_SECRET || "mon_secret_super_dur",
      { expiresIn: "2h" }
    );

    res.json({
      id: user.id,
      login: user.login,
      rôle: user.rôle,
      token,
    });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

module.exports = { app, connection };
