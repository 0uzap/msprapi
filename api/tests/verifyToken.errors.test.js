// Couvre : pas d'en-tête Authorization, format invalide, token invalide.
const request = require('supertest');
const jwt = require('jsonwebtoken');

// IMPORTANT: force FR/US peu importe, /users est toujours actif
const { app } = require('../index');

describe('verifyToken — erreurs', () => {
  it('401 si aucun header Authorization', async () => {
    const res = await request(app).get('/users'); // route protégée
    expect([401, 403]).toContain(res.statusCode);
  });

  it('401 si schéma non-Bearer', async () => {
    const res = await request(app).get('/users').set('Authorization', 'Token abc');
    expect([401, 403]).toContain(res.statusCode);
  });

  it('401 si token invalide (mauvaise signature)', async () => {
    const badToken = jwt.sign({ id: 1 }, 'MAUVAISE_CLE', { expiresIn: '1h' });
    const res = await request(app).get('/users').set('Authorization', `Bearer ${badToken}`);
    expect([401, 403]).toContain(res.statusCode);
  });
});
