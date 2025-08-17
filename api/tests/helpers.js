// tests/helpers.js
const request = require('supertest');
const { app, connection } = require('../index');

/**
 * Force la prochaine requête SQL à échouer (simule un crash DB)
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

module.exports = {
  app,
  connection,
  request,             
  mockOnceQueryReject, 
  getValidToken, 
};

