<?php
// Détecter serveur et langue
$server = $_GET['server'] ?? 'us';
$lang = $_GET['lang'] ?? ($server === 'ch' ? 'fr' : ($server === 'us' ? 'en' : 'fr'));

// URL API selon le serveur
$apiUrls = [
    'us' => 'http://localhost:3020/users/login',
    'fr' => 'http://localhost:3010/users/login',
    'ch' => 'http://localhost:3030/users/login'
];
$apiLoginUrl = $apiUrls[$server] ?? $apiUrls['us'];

// Dictionnaire de traduction
$texts = [
    'fr' => [
        'title'    => 'Connexion',
        'login'    => 'Login',
        'password' => 'Mot de passe',
        'show'     => 'Afficher / Cacher',
        'submit'   => 'Se connecter',
        'back'     => 'Retour à l\'accueil',
        'language' => 'Langue'
    ],
    'en' => [
        'title'    => 'Login',
        'login'    => 'Username',
        'password' => 'Password',
        'show'     => 'Show / Hide',
        'submit'   => 'Login',
        'back'     => 'Back to home',
        'language' => 'Language'
    ],
    'de' => [
        'title'    => 'Anmeldung',
        'login'    => 'Benutzername',
        'password' => 'Passwort',
        'show'     => 'Anzeigen / Verbergen',
        'submit'   => 'Anmelden',
        'back'     => 'Zurück zur Startseite',
        'language' => 'Sprache'
    ],
    'it' => [
        'title'    => 'Accesso',
        'login'    => 'Nome utente',
        'password' => 'Password',
        'show'     => 'Mostra / Nascondi',
        'submit'   => 'Accedi',
        'back'     => 'Torna alla home',
        'language' => 'Lingua'
    ],
];

// Texte en fonction de la langue choisie
$txt = $texts[$lang] ?? $texts['fr'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $txt['title'] ?></title>
    <link rel="stylesheet" href="style_form.css">
</head>
<body>

<header>
    <h2>MSPR 6.1</h2>
</header>

<?php if ($server === 'ch'): ?>
<!-- Sélecteur de langue pour CH uniquement -->
<form method="get" style="margin-bottom: 20px;">
    <input type="hidden" name="server" value="ch">
    <label for="lang"><?= $txt['language'] ?> :</label>
    <select name="lang" id="lang" onchange="this.form.submit()">
        <option value="fr" <?= $lang === 'fr' ? 'selected' : '' ?>>Français</option>
        <option value="de" <?= $lang === 'de' ? 'selected' : '' ?>>Deutsch</option>
        <option value="it" <?= $lang === 'it' ? 'selected' : '' ?>>Italiano</option>
    </select>
</form>
<?php endif; ?>

<form onsubmit="return loginUser(event)">
    <h2><?= $txt['title'] ?></h2>

    <label for="login"><?= $txt['login'] ?> :</label>
    <input type="text" id="login" name="login" required>

    <label for="password"><?= $txt['password'] ?> :</label>
    <input type="password" id="password" name="password" required>

    <!-- Persistance du contexte -->
    <input type="hidden" id="server" value="<?= $server ?>">
    <?php if ($server === 'ch'): ?>
        <input type="hidden" id="lang" value="<?= $lang ?>">
    <?php endif; ?>

    <button type="button" class="form-button" onclick="togglePassword()">
        <?= $txt['show'] ?>
    </button>

    <input type="submit" class="form-button" value="<?= $txt['submit'] ?>">

    <div class="return-link">
        <a href="index.php?server=<?= $server ?><?= $server === 'ch' ? '&lang=' . $lang : '' ?>">
            <button type="button" class="return-button"><?= $txt['back'] ?></button>
        </a>
    </div>

    <p id="responseMessage"></p>
</form>

<script>
function togglePassword() {
    const pwd = document.getElementById("password");
    pwd.type = pwd.type === "password" ? "text" : "password";
}

async function loginUser(event) {
    event.preventDefault();

    const login = document.getElementById("login").value;
    const password = document.getElementById("password").value;
    const serverVal = document.getElementById("server").value;
    const langVal = document.getElementById("lang") ? document.getElementById("lang").value : "";
    const message = document.getElementById("responseMessage");

    const apiUrl = <?= json_encode($apiLoginUrl) ?>;

    try {
        const response = await fetch(apiUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ login: login, mdp: password })
        });

        const data = await response.json();

        if (response.ok) {
            localStorage.setItem("user", JSON.stringify(data));
            localStorage.setItem("server", serverVal);
            if (langVal) localStorage.setItem("lang", langVal);

            // Enregistrer le token si serveur FR
            if (serverVal === "fr" && data.token) {
                localStorage.setItem("token", data.token);
            }

            // Redirection selon serveur
            let redirectUrl = "";
            switch (serverVal) {
                case "us":
                    redirectUrl = "index_co.php?server=us";
                    break;
                case "fr":
                    redirectUrl = "index_co_fr.php?server=fr";
                    break;
                case "ch":
                    redirectUrl = `index_co.php?server=ch&lang=${langVal}`;
                    break;
                default:
                    redirectUrl = "index_co.php?server=us";
            }
            window.location.href = redirectUrl;
        } else {
            message.textContent = data.error || "Erreur lors de la connexion.";
            message.style.color = "red";
        }
    } catch (err) {
        message.textContent = "Erreur de connexion au serveur.";
        message.style.color = "red";
    }

    return false;
}
</script>

</body>
</html>
