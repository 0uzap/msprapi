<?php
$lang = 'fr';
$t = [
    'title'          => 'MSPR 6.3',
    'heading'        => 'Accès aux graphiques',
    'ai_predictions' => "Voir les prédictions de l'IA",
    'register'       => 'Inscription d\'un utilisateur',
    'logout'         => 'Déconnexion',
    'login_msg'      => 'Connecté en tant que :'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $t['title'] ?></title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<header id="header">
    <h2><?= $t['title'] ?></h2>
</header>

<h1><?= $t['heading'] ?></h1>

<!-- ✅ Bouton lecture vocale -->
<button id="readPageBtn" class="accessibility-button" aria-label="Lecture de la page">🔊 Lecture vocale</button>

<div class="button-container">
    <a id="aiLink" href="#"><button><?= $t['ai_predictions'] ?></button></a>
</div>

<script>
// Expose translations to JS
const texts = {
  loginMsg: <?= json_encode($t['login_msg']) ?>,
  logout:   <?= json_encode($t['logout']) ?>,
  register: <?= json_encode($t['register']) ?>
};

// Récupère user & server
const user   = JSON.parse(localStorage.getItem("user"));
const server = localStorage.getItem("server") || "fr";

// Met à jour les liens
document.getElementById("aiLink").href = `prediGraph.php?server=${server}`;

// Affichage selon authentification
if (!user) {
  document.body.innerHTML = `
    <p style="text-align:center;color:red;">
      Veuillez vous connecter pour accéder à cette page.
    </p>
    <div style="text-align:center;">
      <a href="form_fr.php?server=${server}">
        <button>Se connecter</button>
      </a>
    </div>`;
} else {
  const header = document.getElementById("header");
  // Message de connexion
  const info = document.createElement("p");
  info.textContent = texts.loginMsg + " " + user.login;
  info.style.color = "green";
  header.appendChild(info);
  // Bouton Déconnexion
  const logoutBtn = document.createElement("button");
  logoutBtn.textContent = texts.logout;
  logoutBtn.className = "form-button";
  logoutBtn.onclick = () => {
    localStorage.removeItem("user");
    localStorage.removeItem("server");
    window.location.href = `index.php?server=${server}`;
  };
  header.appendChild(logoutBtn);
  // Bouton Inscription (admin)
  if (user.rôle === "admin") {
    const adminBtn = document.createElement("button");
    adminBtn.textContent = texts.register;
    adminBtn.className = "form-button";
    adminBtn.onclick = () => {
      window.location.href = `inscription.php?server=${server}`;
    };
    header.appendChild(adminBtn);
  }
}

// Lecture vocale
document.getElementById("readPageBtn").addEventListener("click", () => {
    window.speechSynthesis.cancel();
    const content = document.body.innerText;
    const utterance = new SpeechSynthesisUtterance(content);
    utterance.lang = "fr-FR";
    utterance.rate = 1;
    utterance.pitch = 1;
    utterance.volume = 1;
    window.speechSynthesis.speak(utterance);
});
</script>

</body>
</html>
