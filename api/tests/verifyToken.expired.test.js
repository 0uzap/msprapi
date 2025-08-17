process.env.PAYS_CIBLE = 'US';
jest.resetModules();

const { app, request } = require('./helpers'); // on garde app/request depuis helpers
const jwt = require('jsonwebtoken');

// Génère un JWT déjà expiré (exp dans le passé), avec la même clé que l'app
const SECRET = process.env.JWT_SECRET || 'mon_secret_super_dur';
function getExpiredToken() {
  const now = Math.floor(Date.now() / 1000);
  return jwt.sign(
    { id: -1, login: 'exp', rôle: 'admin', iat: now - 20, exp: now - 10 },
    SECRET
  );
}

describe('verifyToken — token expiré', () => {
  it('renvoie 401/403 quand le token est expiré', async () => {
    const expired = getExpiredToken();

    const res = await request(app)
      .get('/users')
      .set('Authorization', `Bearer ${expired}`);

    expect([401, 403]).toContain(res.statusCode);
  });
});
