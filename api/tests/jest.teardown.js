// tests/jest.teardown.js
module.exports = async () => {
  const { connection } = require('./helpers');
  try {
    await connection.end();
    // petite marge pour libérer les sockets
    await new Promise(r => setTimeout(r, 10));
  } catch {}
};
