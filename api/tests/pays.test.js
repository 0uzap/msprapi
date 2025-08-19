process.env.PAYS_CIBLE = 'US';
jest.resetModules();
const { app, connection, request, mockOnceQueryReject } = require('./helpers');


describe("API /pays - CRUD + erreurs", () => {
  beforeAll(async () => {
    await connection.query(
      "INSERT IGNORE INTO continent (idContinent, continent) VALUES (77,'UnitTest')"
    );
  });

  it("POST /pays => 400 si manquant", async () => {
    const bad = await request(app).post("/pays").send({ id_pays: 777 });
    expect(bad.statusCode).toBe(400);
  });

  it("POST /pays => 201 OK", async () => {
    const res = await request(app)
      .post("/pays")
      .send({ id_pays: 777, pays: "Zland", idContinent: 77 });
    expect([200, 201]).toContain(res.statusCode);
  });

  it("GET /pays => 200 + array", async () => {
    const list = await request(app).get("/pays");
    expect(list.statusCode).toBe(200);
    expect(Array.isArray(list.body)).toBe(true);
  });

  it("GET /pays/:id => 200 + objet joint", async () => {
    const one = await request(app).get("/pays/777");
    expect(one.statusCode).toBe(200);
    expect(one.body).toHaveProperty("pays", "Zland");
    expect(one.body).toHaveProperty("continent", "UnitTest");
  });

  it("PUT /pays/:id => 200 OK", async () => {
    const upd = await request(app)
      .put("/pays/777")
      .send({ pays: "Zlandia", idContinent: 77 });
    expect(upd.statusCode).toBe(200);
  });

  it("GET /pays => 500 si erreur SQL", async () => {
    const restore = mockOnceQueryReject("DOWN");
    const res = await request(app).get("/pays");
    expect(res.statusCode).toBe(500);
    expect(res.body).toHaveProperty("error");
    restore();
  });

  it("DELETE /pays/:id => 200 OK", async () => {
    const del = await request(app).delete("/pays/777");
    expect(del.statusCode).toBe(200);
  });

  afterAll(async () => {
    await connection.query("DELETE FROM pays WHERE id_pays=777");
    await connection.query("DELETE FROM continent WHERE idContinent=77");
    await connection.end();
  });
});
