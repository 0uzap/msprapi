process.env.PAYS_CIBLE = 'US';
jest.resetModules();
const { app, connection, request, mockOnceQueryReject } = require('./helpers');


describe("API /coronavirus_daily - CRUD + erreurs", () => {
  beforeAll(async () => {
    await connection.query(
      "INSERT IGNORE INTO continent (idContinent, continent) VALUES (55,'CVD')"
    );
    await connection.query(
      "INSERT IGNORE INTO pays (id_pays, pays, idContinent) VALUES (555,'CVCOUNTRY',55)"
    );
  });

  it("POST => 400 si manquant", async () => {
    const bad = await request(app)
      .post("/coronavirus_daily")
      .send({ date: "2023-01-02" });
    expect(bad.statusCode).toBe(400);
  });

  let createdId;

  it("POST => 201 OK", async () => {
    const res = await request(app).post("/coronavirus_daily").send({
      date: "2023-01-02",
      cumulCasTotaux: 1000,
      nouveauCasJournalier: 10,
      id_pays: 555,
      idContinent: 55,
    });
    expect([200, 201]).toContain(res.statusCode);
    createdId = res.body.id;
  });

  it("GET all => 200", async () => {
    const list = await request(app).get("/coronavirus_daily");
    expect(list.statusCode).toBe(200);
    expect(Array.isArray(list.body)).toBe(true);
  });

  it("GET id => 200 + jointures", async () => {
    const one = await request(app).get(`/coronavirus_daily/${createdId}`);
    expect(one.statusCode).toBe(200);
    expect(one.body).toHaveProperty("pays", "CVCOUNTRY");
  });

  it("PUT id => 200", async () => {
    const upd = await request(app)
      .put(`/coronavirus_daily/${createdId}`)
      .send({ nouveauCasJournalier: 11 });
    expect(upd.statusCode).toBe(200);
  });

  it("GET /coronavirus_daily => 500 si erreur SQL", async () => {
    const restore = mockOnceQueryReject("DOWN");
    const res = await request(app).get("/coronavirus_daily");
    expect(res.statusCode).toBe(500);
    expect(res.body).toHaveProperty("error");
    restore();
  });

  it("DELETE id => 200", async () => {
    const del = await request(app).delete(`/coronavirus_daily/${createdId}`);
    expect(del.statusCode).toBe(200);
  });

  afterAll(async () => {
    await connection.query(
      "DELETE FROM coronavirus_daily WHERE id_pays=555"
    );
    await connection.query("DELETE FROM pays WHERE id_pays=555");
    await connection.query("DELETE FROM continent WHERE idContinent=55");
    await connection.end();
  });
});
