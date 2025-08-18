require("dotenv").config();

const express = require("express");
const app = express();
const YAML = require("yamljs");
const swaggerUi = require("swagger-ui-express");
const swaggerDocument = YAML.load("./swagger.yaml");
const verifyToken = require("./middleware/verifyToken");

const pays = process.env.PAYS_CIBLE;
console.log("🌍 Environnement pays :", pays ?? "aucun");
console.log("🌍 pays brut =", pays, " typeof=", typeof pays);


const mysql = require("mysql2/promise");
const dbHost = process.env.DB_HOST || `localhost`;

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


app.get("/users", verifyToken, async (req, res) => {
  try {
    const [results] = await connection.query("SELECT * FROM users");
    res.json(results);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});


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

app.delete("/users/:id", verifyToken, async (req, res) => {
  try {
    await connection.query("DELETE FROM users WHERE id = ?", [req.params.id]);
    res.json({ message: "Utilisateur supprimé avec succès" });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});


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
