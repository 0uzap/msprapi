<?php
$lang = $_GET['lang'] ?? 'fr';
$texts = [
    'fr' => [
        'title' => 'MSPR 6.1',
        'heading' => 'Accès aux graphiques',
        'daily' => 'Coronavirus journalier',
        'register' => 'Inscription d\'un utilisateur',
        'logout' => 'Déconnexion',
        'language' => 'Langue',
        'login_msg' => 'Connecté en tant que :'
    ],
    'de' => [
        'title' => 'MSPR 6.1',
        'heading' => 'Grafikzugriff',
        'daily' => 'Coronavirus täglich',
        'register' => 'Benutzerregistrierung',
        'logout' => 'Abmelden',
        'language' => 'Sprache',
        'login_msg' => 'Angemeldet als:'
    ],
    'it' => [
        'title' => 'MSPR 6.1',
        'heading' => 'Accesso ai grafici',
        'daily' => 'Coronavirus giornaliero',
        'register' => 'Registrazione utente',
        'logout' => 'Disconnessione',
        'language' => 'Lingua',
        'login_msg' => 'Connesso come:'
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

<form method="get" style="margin-bottom: 20px;">
    <label for="lang"><?= $t['language'] ?> :</label>
    <select name="lang" id="lang" onchange="this.form.submit()">
        <option value="fr" <?= $lang === 'fr' ? 'selected' : '' ?>>Français</option>
        <option value="de" <?= $lang === 'de' ? 'selected' : '' ?>>Deutsch</option>
        <option value="it" <?= $lang === 'it' ? 'selected' : '' ?>>Italiano</option>
    </select>
</form>

<div class="button-container">
    <a href="graph.php"><button><?= $t['daily'] ?></button></a>
</div>

<script>
    const user = JSON.parse(localStorage.getItem("user"));
    const header = document.getElementById("header");

    if (!user) {
        document.body.innerHTML = "<p style='text-align:center; color:red;'>Veuillez vous connecter pour accéder à cette page.</p><div style='text-align:center;'><a href='form_ch.php'><button>Se connecter</button></a></div>";
    } else {
        const info = document.createElement("p");
        info.textContent = "<?= $t['login_msg'] ?> " + user.login;
        info.style.color = "green";
        header.appendChild(info);

        const logoutBtn = document.createElement("button");
        logoutBtn.textContent = "<?= $t['logout'] ?>";
        logoutBtn.className = "form-button";
        logoutBtn.onclick = () => {
            localStorage.removeItem("user");
            window.location.href = "index.php";
        };
        header.appendChild(logoutBtn);

        if (user.rôle === "admin") {
            const adminBtn = document.createElement("button");
            adminBtn.textContent = "<?= $t['register'] ?>";
            adminBtn.className = "form-button";
            adminBtn.onclick = () => window.location.href = "inscription.php";
            header.appendChild(adminBtn);
        }
    }
</script>

</body>
</html>
