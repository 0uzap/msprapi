process.env.PAYS_CIBLE = 'CH';
jest.resetModules();

const request = require('supertest');
const { app, connection } = require('../index');

describe('CH => seules routes /users actives', () => {
  it('GET /continents => 404 ; POST /users/login (existe, 400 si vide)', async () => {
    const nf = await request(app).get('/continents');
    expect([404, 400]).toContain(nf.statusCode);

    const bad = await request(app).post('/users/login').send({});
    expect(bad.statusCode).toBe(400);
  });

  afterAll(async () => { await connection.end(); });
});
