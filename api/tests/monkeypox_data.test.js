process.env.PAYS_CIBLE = "US";
jest.resetModules();

const { app, connection, request, mockOnceQueryReject } = require("./helpers");

describe("API /monkeypox_data - CRUD + erreurs", () => {
  beforeAll(async () => {
    await connection.query(
      "INSERT IGNORE INTO continent (idContinent, continent) VALUES (66,'MPX')"
    );
    await connection.query(
      "INSERT IGNORE INTO pays (id_pays, pays, idContinent) VALUES (666,'MPXCOUNTRY',66)"
    );
  });

  it("POST => 400 si manquant", async () => {
    const bad = await request(app)
      .post("/monkeypox_data")
      .send({ date: "2023-01-01" });
    expect(bad.statusCode).toBe(400);
  });

  let createdId;

  it("POST => 201 OK", async () => {
    const res = await request(app).post("/monkeypox_data").send({
      date: "2023-01-01",
      nbCasTotaux: 50,
      nbMortTotaux: 5,
      id_pays: 666,
      idContinent: 66,
    });
    expect([200, 201]).toContain(res.statusCode);
    createdId = res.body.id;
  });

  it("GET all => 200", async () => {
    const list = await request(app).get("/monkeypox_data");
    expect(list.statusCode).toBe(200);
    expect(Array.isArray(list.body)).toBe(true);
  });

  it("GET id => 200 + jointures", async () => {
    const one = await request(app).get(`/monkeypox_data/${createdId}`);
    expect(one.statusCode).toBe(200);
    expect(one.body).toHaveProperty("pays", "MPXCOUNTRY");
  });

  it("PUT id => 200", async () => {
    const upd = await request(app)
      .put(`/monkeypox_data/${createdId}`)
      .send({ nbCasTotaux: 55 });
    expect(upd.statusCode).toBe(200);
  });

  it("GET /monkeypox_data => 500 si erreur SQL", async () => {
    const restore = mockOnceQueryReject("DOWN");
    const res = await request(app).get("/monkeypox_data");
    expect(res.statusCode).toBe(500);
    expect(res.body).toHaveProperty("error");
    restore();
  });

  it("DELETE id => 200", async () => {
    const del = await request(app).delete(`/monkeypox_data/${createdId}`);
    expect(del.statusCode).toBe(200);
  });

  afterAll(async () => {
    await connection.query("DELETE FROM monkeypox_data WHERE id_pays=666");
    await connection.query("DELETE FROM pays WHERE id_pays=666");
    await connection.query("DELETE FROM continent WHERE idContinent=66");
    await connection.end();
  });
});
