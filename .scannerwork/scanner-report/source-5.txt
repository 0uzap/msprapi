const { app } = require('./index');
const port = process.env.PORT || 3001;

app.listen(port, () => {
  console.log(`🚀 API démarrée sur http://localhost:${port}`);
});
