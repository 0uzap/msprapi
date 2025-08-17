process.env.PAYS_CIBLE = 'US';
jest.resetModules();
const { app, connection, request, mockOnceQueryReject } = require('./helpers');


describe("API /covid_country - CRUD + erreurs", () => {
  beforeAll(async () => {
    await connection.query(
      "INSERT IGNORE INTO continent (idContinent, continent) VALUES (88,'COV')"
    );
    await connection.query(
      "INSERT IGNORE INTO pays (id_pays, pays, idContinent) VALUES (888,'COVCOUNTRY',88)"
    );
  });

  it("POST => 400 si manquant", async () => {
    const bad = await request(app).post("/covid_country").send({ nbCas: 1 });
    expect(bad.statusCode).toBe(400);
  });

  let createdId;

  it("POST => 201 OK", async () => {
    const res = await request(app).post("/covid_country").send({
      nbCas: 10,
      nbMort: 1,
      nbSoigne: 3,
      id_pays: 888,
      idContinent: 88,
    });
    expect([200, 201]).toContain(res.statusCode);
    createdId = res.body.id;
  });

  it("GET all => 200", async () => {
    const list = await request(app).get("/covid_country");
    expect(list.statusCode).toBe(200);
    expect(Array.isArray(list.body)).toBe(true);
  });

  it("GET id => 200 + jointures", async () => {
    const one = await request(app).get(`/covid_country/${createdId}`);
    expect(one.statusCode).toBe(200);
    expect(one.body).toHaveProperty("pays", "COVCOUNTRY");
  });

  it("PUT id => 200", async () => {
    const upd = await request(app)
      .put(`/covid_country/${createdId}`)
      .send({ nbCas: 11 });
    expect(upd.statusCode).toBe(200);
  });

  it("GET /covid_country => 500 si erreur SQL", async () => {
    const restore = mockOnceQueryReject("DOWN");
    const res = await request(app).get("/covid_country");
    expect(res.statusCode).toBe(500);
    expect(res.body).toHaveProperty("error");
    restore();
  });

  it("DELETE id => 200", async () => {
    const del = await request(app).delete(`/covid_country/${createdId}`);
    expect(del.statusCode).toBe(200);
  });

  afterAll(async () => {
    await connection.query(
      "DELETE FROM covid_country WHERE id_pays=888 AND idContinent=88"
    );
    await connection.query("DELETE FROM pays WHERE id_pays=888");
    await connection.query("DELETE FROM continent WHERE idContinent=88");
    await connection.end();
  });
});
