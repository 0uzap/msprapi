process.env.PAYS_CIBLE = 'FR';
jest.resetModules();

const request = require('supertest');
const { app, connection } = require('../index');

describe("FR/CH => seules routes /users actives", () => {
  it("GET /continents => 404 ; POST /users/login existe", async () => {
    const nf = await request(app).get('/continents');
    expect([404,400]).toContain(nf.statusCode);

    const bad = await request(app).post('/users/login').send({}); // existe mais 400 si vide
    expect(bad.statusCode).toBe(400);
  });

  afterAll(async () => {
    await connection.end();
  });
});
