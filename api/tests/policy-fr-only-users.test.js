// FR
process.env.PAYS_CIBLE = 'FR';
jest.resetModules();
const { app, request } = require('./helpers');


describe("FR/CH => seules routes /users actives", () => {
  it("GET /continents => 404 ; POST /users/login existe", async () => {
    const nf = await request(app).get('/continents');
    expect([404,400]).toContain(nf.statusCode);

    const bad = await request(app).post('/users/login').send({}); // existe mais 400 si vide
    expect(bad.statusCode).toBe(400);
  });


});
