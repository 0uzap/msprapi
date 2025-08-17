// Active toutes les routes
process.env.PAYS_CIBLE = "US";
jest.resetModules();

// IMPORTANT: on prend tout depuis le helper (app, connection, request, mock)
const { app, connection, request, mockOnceQueryReject } = require("./helpers");

describe("API /continents - CRUD + erreurs", () => {
  it("POST /continents => 201 OK", async () => {
    const create = await request(app)
      .post("/continents")
      .send({ idContinent: 99, continent: "Testinent" });
    expect([200, 201]).toContain(create.statusCode);
  });

  it("POST /continents => 400 si manquant", async () => {
    const bad = await request(app).post("/continents").send({ continent: "no id" });
    expect(bad.statusCode).toBe(400);
  });

  it("GET /continents => 200 + array", async () => {
    const list = await request(app).get("/continents");
    expect(list.statusCode).toBe(200);
    expect(Array.isArray(list.body)).toBe(true);
  });

  it("GET /continents/:id => 200 + objet", async () => {
    const one = await request(app).get("/continents/99");
    expect(one.statusCode).toBe(200);
    expect(one.body).toHaveProperty("continent", "Testinent");
  });

  it("PUT /continents/:id => 200 OK", async () => {
    const upd = await request(app).put("/continents/99").send({ continent: "Testinent2" });
    expect(upd.statusCode).toBe(200);

    const check = await request(app).get("/continents/99");
    expect(check.body.continent).toBe("Testinent2");
  });

  it("GET /continents => 500 si erreur SQL", async () => {
    const restore = mockOnceQueryReject("DOWN");
    const res = await request(app).get("/continents");
    expect(res.statusCode).toBe(500);
    expect(res.body).toHaveProperty("error");
    restore();
  });

  it("DELETE /continents/:id => 200 OK", async () => {
    const del = await request(app).delete("/continents/99");
    expect(del.statusCode).toBe(200);
  });

  afterAll(async () => {
    await connection.query("DELETE FROM continent WHERE idContinent=99");
    await connection.end();
  });
});
