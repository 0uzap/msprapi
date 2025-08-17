// tests/helpers.js
const request = require('supertest');
const { app, connection } = require('../index');

/**
 * Simule une erreur SQL sur la prochaine requête (status 500 attendu côté API).
 * Retourne une fonction restore() pour remettre le comportement normal.
 */
function mockOnceQueryReject(msg = 'DB error') {
  const original = connection.query.bind(connection);
  const spy = jest.spyOn(connection, 'query').mockRejectedValueOnce(new Error(msg));
  return () => {
    try { spy.mockRestore?.(); } catch {}
    connection.query = original;
  };
}

/**
 * Retourne un token JWT valide via /users/login.
 * Insère un utilisateur de seed s'il n'existe pas.
 * Mot de passe en clair: "password123" (hash déjà dans la BDD).
 */
async function getValidToken() {
  await connection.query(`
    INSERT IGNORE INTO users (login, mdp, rôle)
    VALUES (
      'token_admin',
      '$2b$10$8jCs6UK.y/T8V6hcgjlGzOZYwWsPdBtCTEJPC/MBCLDy8gio0d/C6', -- hash de "password123"
      'admin'
    )
  `);

  const res = await request(app).post('/users/login').send({
    login: 'token_admin',
    mdp: 'password123',
  });

  if (res.statusCode !== 200 || !res.body?.token) {
    throw new Error(`Login seed token_admin KO (status ${res.statusCode}) body=${JSON.stringify(res.body)}`);
  }
  return res.body.token;
}

module.exports = {
  app,
  connection,
  request,              // supertest
  mockOnceQueryReject,  // pour forcer un 500 SQL
  getValidToken,        // pour obtenir un JWT valide si besoin
};
