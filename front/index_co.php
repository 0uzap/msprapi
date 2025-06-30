<?php
// 1) Détection du serveur via GET uniquement
$serverType = $_GET['server'] ?? 'us';
if (!in_array($serverType, ['us', 'fr', 'ch'])) {
    $serverType = 'us';
}

// 2) Vérification du token si serveur = fr
$isTokenVerified = false;

if ($serverType === 'fr') {
    if (empty($_COOKIE['token'])) {
        header('Location: form.php?server=fr');
        exit;
    }

    $opts = ['http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer {$_COOKIE['token']}\r\n"
    ]];
    $ctx = stream_context_create($opts);
    $verify = @file_get_contents('http://localhost:3010/verify', false, $ctx);

    if ($verify !== 'OK') {
        setcookie('token', '', time() - 3600, '/');
        header('Location: form.php?server=fr');
        exit;
    }

    $isTokenVerified = true; // <- confirmation que la vérification a réussi
}

// 3) Détection de la langue
$lang = $_GET['lang'] ?? ($serverType === 'ch' ? 'fr' : ($serverType === 'us' ? 'en' : 'fr'));

// 4) Préparation baseQuery
$baseQuery = "server=$serverType";
if ($serverType === 'ch') {
    $baseQuery .= "&lang=$lang";
}

// 5) Dictionnaire de traduction
$texts = [
    'fr' => [
        'title'     => 'MSPR 6.1',
        'heading'   => 'Accès aux graphiques',
        'world'     => 'Coronavirus monde',
        'daily'     => 'Coronavirus journalier',
        'monkeypox' => 'Monkeypox',
        'register'  => 'Inscription d\'un utilisateur',
        'logout'    => 'Déconnexion',
        'language'  => 'Langue',
        'login_msg' => 'Connecté en tant que :',
        'ai_predictions' => "Voir les prédictions de l'IA",
        'token_verified' => "✅ Token vérifié avec succès"
    ],
    'en' => [
        'title'     => 'MSPR 6.1',
        'heading'   => 'Graph Access',
        'world'     => 'Coronavirus Global',
        'daily'     => 'Coronavirus Daily',
        'monkeypox' => 'Monkeypox',
        'register'  => 'Register a User',
        'logout'    => 'Logout',
        'language'  => 'Language',
        'login_msg' => 'Logged in as:',
        'ai_predictions' => "See AI predictions",
        'token_verified' => "✅ Token successfully verified"
    ],
    'de' => [
        'title'     => 'MSPR 6.1',
        'heading'   => 'Grafikzugriff',
        'world'     => 'Coronavirus Weltweit',
        'daily'     => 'Coronavirus täglich',
        'monkeypox' => 'Affenpocken',
        'register'  => 'Benutzerregistrierung',
        'logout'    => 'Abmelden',
        'language'  => 'Sprache',
        'login_msg' => 'Angemeldet als:',
        'ai_predictions' => "KI-Vorhersagen anzeigen",
        'token_verified' => "✅ Token erfolgreich überprüft"
    ],
    'it' => [
        'title'     => 'MSPR 6.1',
        'heading'   => 'Accesso ai grafici',
        'world'     => 'Coronavirus mondo',
        'daily'     => 'Coronavirus giornaliero',
        'monkeypox' => 'Vaiolo delle scimmie',
        'register'  => 'Registrazione utente',
        'logout'    => 'Disconnessione',
        'language'  => 'Lingua',
        'login_msg' => 'Connesso come:',
        'ai_predictions' => "Visualizza le previsioni dell'IA",
        'token_verified' => "✅ Token verificato con successo"
    ]
];

$t = $texts[$lang] ?? $texts['fr'];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $t['title'] ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header id="header">
    <h2><?= $t['title'] ?></h2>
</header>

<h1><?= $t['heading'] ?></h1>

<?php if ($serverType === 'fr' && $isTokenVerified): ?>
  <p style="text-align: center; color: green;"><?= $t['token_verified'] ?></p>
<?php endif; ?>

<?php if ($serverType === 'ch'): ?>
<form method="get" style="margin-bottom: 20px;">
    <input type="hidden" name="server" value="ch">
    <label for="lang"><?= $t['language'] ?> :</label>
    <select name="lang" id="lang" onchange="this.form.submit()">
        <option value="fr" <?= $lang==='fr'?'selected':'' ?>>Français</option>
        <option value="de" <?= $lang==='de'?'selected':'' ?>>Deutsch</option>
        <option value="it" <?= $lang==='it'?'selected':'' ?>>Italiano</option>
    </select>
</form>
<?php endif; ?>

<div class="button-container" id="buttons">
    <?php if (in_array($serverType, ['fr', 'ch'])): ?>
        <a href="prediGraph.php?<?= $baseQuery ?>"><button><?= $t['ai_predictions'] ?></button></a>
    <?php endif; ?>

    <?php if ($serverType === 'us'): ?>
        <a href="graph.php?<?= $baseQuery ?>"><button><?= $t['daily'] ?></button></a>
        <a href="graph2.php?<?= $baseQuery ?>"><button><?= $t['world'] ?></button></a>
        <a href="graph3.php?<?= $baseQuery ?>"><button><?= $t['monkeypox'] ?></button></a>
    <?php endif; ?>
</div>

<script>
    const user = JSON.parse(localStorage.getItem("user"));
    const header = document.getElementById("header");
    const server = localStorage.getItem("server") || "<?= $serverType ?>";
    const lang   = localStorage.getItem("lang")   || "<?= $lang ?>";

    if (!user) {
        document.body.innerHTML = `
            <p style='text-align:center;color:red;'>
              <?= $lang==='fr'? 
                  'Veuillez vous connecter pour accéder à cette page.' 
                : ($lang==='en'? 
                  'Please log in to access this page.' 
                : ($lang==='de'? 
                  'Bitte melden Sie sich an, um auf diese Seite zuzugreifen.' 
                : 'Effettua il login per accedere a questa pagina.')) ?>
            </p>
            <div style='text-align:center;'>
                <a href='form.php?server=${server}${server==='ch'? '&lang='+lang : ''}'>
                  <button><?= $t['logout'] ?></button>
                </a>
            </div>`;
    } else {
        const info = document.createElement("p");
        info.textContent = "<?= $t['login_msg'] ?> " + user.login;
        info.style.color = "green";
        info.style.marginTop = "10px";
        header.appendChild(info);

        const logoutBtn = document.createElement("button");
        logoutBtn.textContent = "<?= $t['logout'] ?>";
        logoutBtn.className = "form-button";
        logoutBtn.onclick = () => {
            localStorage.removeItem("user");
            localStorage.removeItem("server");
            localStorage.removeItem("lang");
            if (server === 'fr') document.cookie = 'token=; Max-Age=0; path=/';
            window.location.href = "index.php?<?= $baseQuery ?>";
        };
        header.appendChild(logoutBtn);

        if (user.rôle === "admin") {
            const adminBtn = document.createElement("button");
            adminBtn.textContent = "<?= $t['register'] ?>";
            adminBtn.className = "form-button";
            adminBtn.onclick = () => window.location.href = "inscription.php?<?= $baseQuery ?>";
            header.appendChild(adminBtn);
        }
    }
</script>

</body>
</html>
