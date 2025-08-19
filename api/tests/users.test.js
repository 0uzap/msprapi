const request = require('supertest');
const { app, connection } = require('../index');

describe("Test de l'API /users", () => {
  let token;

  beforeAll(async () => {
    // On insère un utilisateur de test dans la BDD (en seed)
    await connection.query(`
      INSERT IGNORE INTO users (login, mdp, rôle)
      VALUES (
        'testuser',
        '$2b$10$8jCs6UK.y/T8V6hcgjlGzOZYwWsPdBtCTEJPC/MBCLDy8gio0d/C6', -- hash de "password123"
        'admin'
      )
    `);

    // ensuite on fait le login pour récupérer le token JWT
    const res = await request(app)
      .post("/users/login")
      .send({
        login: "testuser",
        mdp: "password123"
      });

    token = res.body.token;
  });

  it("GET /users => 200 OK et tableau", async () => {
    const res = await request(app)
      .get("/users")
      .set("Authorization", `Bearer ${token}`); // injecte le token

    expect(res.statusCode).toBe(200);
    expect(Array.isArray(res.body)).toBe(true);
  });

  it("POST /users => création utilisateur", async () => {
    const user = {
      login: "nouveluser",
      mdp: "pass123",
      rôle: "admin"
    };

    const res = await request(app)
      .post("/users")
      .send(user);

    expect(res.statusCode).toBe(201);
    expect(res.body).toHaveProperty("id");
    expect(res.body.login).toBe("nouveluser");
    expect(res.body.rôle).toBe("admin");
  });

  afterAll(async () => {
    // Nettoyage : on ferme la connexion MySQL
    await connection.end();
  });
});
