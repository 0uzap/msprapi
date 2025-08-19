<?php
// Détection du serveur via port
$host = $_SERVER['HTTP_HOST'];
$serverType = 'us'; // par défaut

if (strpos($host, '3010') !== false) $serverType = 'fr';
elseif (strpos($host, '3030') !== false) $serverType = 'ch';

// Langue par défaut selon serveur
$lang = $_GET['lang'] ?? ($serverType === 'ch' ? 'fr' : ($serverType === 'us' ? 'en' : 'fr'));

// Textes multilingues
$texts = [
    'fr' => [
        'title' => 'Inscription',
        'header' => 'MSPR 6.3',
        'heading' => 'Inscription',
        'login' => 'Login :',
        'password' => 'Mot de passe :',
        'confirm' => 'Confirmer le mot de passe :',
        'show_hide' => 'Afficher / Cacher',
        'role' => 'Rôle :',
        'user' => 'Utilisateur',
        'admin' => 'Admin',
        'submit' => 'S\'inscrire',
        'back' => 'Retour à l\'accueil',
        'error_mismatch' => 'Les mots de passe ne correspondent pas.',
        'success' => 'Inscription réussie ! Redirection...',
        'error_generic' => 'Erreur :',
        'error_connection' => 'Erreur de connexion à l’API.',
        'language' => 'Langue'
    ],
    'en' => [
        'title' => 'Register',
        'header' => 'MSPR 6.3',
        'heading' => 'Register',
        'login' => 'Login:',
        'password' => 'Password:',
        'confirm' => 'Confirm password:',
        'show_hide' => 'Show / Hide',
        'role' => 'Role:',
        'user' => 'User',
        'admin' => 'Admin',
        'submit' => 'Register',
        'back' => 'Back to home',
        'error_mismatch' => 'Passwords do not match.',
        'success' => 'Registration successful! Redirecting...',
        'error_generic' => 'Error:',
        'error_connection' => 'Error connecting to API.',
        'language' => 'Language'
    ],
    'de' => [
        'title' => 'Registrierung',
        'header' => 'MSPR 6.3',
        'heading' => 'Registrierung',
        'login' => 'Login:',
        'password' => 'Passwort:',
        'confirm' => 'Passwort bestätigen:',
        'show_hide' => 'Anzeigen / Verbergen',
        'role' => 'Rolle:',
        'user' => 'Benutzer',
        'admin' => 'Admin',
        'submit' => 'Registrieren',
        'back' => 'Zurück zur Startseite',
        'error_mismatch' => 'Passwörter stimmen nicht überein.',
        'success' => 'Registrierung erfolgreich! Weiterleitung...',
        'error_generic' => 'Fehler:',
        'error_connection' => 'Verbindungsfehler zur API.',
        'language' => 'Sprache'
    ],
    'it' => [
        'title' => 'Registrazione',
        'header' => 'MSPR 6.3',
        'heading' => 'Registrazione',
        'login' => 'Login:',
        'password' => 'Password:',
        'confirm' => 'Conferma password:',
        'show_hide' => 'Mostra / Nascondi',
        'role' => 'Ruolo:',
        'user' => 'Utente',
        'admin' => 'Admin',
        'submit' => 'Registrati',
        'back' => 'Torna alla home',
        'error_mismatch' => 'Le password non corrispondono.',
        'success' => 'Registrazione riuscita! Reindirizzamento...',
        'error_generic' => 'Errore:',
        'error_connection' => 'Errore di connessione all\'API.',
        'language' => 'Lingua'
    ]
];

if (!isset($texts[$lang])) $lang = 'fr';
$t = $texts[$lang];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $t['title'] ?></title>
    <link rel="stylesheet" href="style_form.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<header>
    <h2><?= $t['header'] ?></h2>
</header>

<?php if ($serverType === 'ch'): ?>
<form method="get" style="margin-bottom: 20px;">
    <label for="lang"><?= $t['language'] ?> :</label>
    <select name="lang" id="lang" onchange="this.form.submit()">
        <option value="fr" <?= $lang === 'fr' ? 'selected' : '' ?>>Français</option>
        <option value="de" <?= $lang === 'de' ? 'selected' : '' ?>>Deutsch</option>
        <option value="it" <?= $lang === 'it' ? 'selected' : '' ?>>Italiano</option>
    </select>
</form>
<?php endif; ?>

<form id="registerForm" onsubmit="return registerUser(event)">
    <h2><?= $t['heading'] ?></h2>

    <label for="login"><?= $t['login'] ?></label>
    <input type="text" id="login" name="login" required>

    <label for="password"><?= $t['password'] ?></label>
    <input type="password" id="password" name="password" required>
    <button type="button" class="form-button" onclick="togglePassword('password')"><?= $t['show_hide'] ?></button>

    <label for="confirm_password"><?= $t['confirm'] ?></label>
    <input type="password" id="confirm_password" name="confirm_password" required>
    <button type="button" class="form-button" onclick="togglePassword('confirm_password')"><?= $t['show_hide'] ?></button>

    <label for="role"><?= $t['role'] ?></label>
    <select id="role" name="role" class="form-select" required>
        <option value="utilisateur"><?= $t['user'] ?></option>
        <option value="admin"><?= $t['admin'] ?></option>
    </select>

    <input type="submit" class="form-button" value="<?= $t['submit'] ?>">

    <div class="return-link">
        <a href="index.php"><button type="button" class="return-button"><?= $t['back'] ?></button></a>
    </div>

    <p id="responseMessage"></p>
</form>

<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        input.type = input.type === "password" ? "text" : "password";
    }

    function getApiUrl() {
        const port = window.location.port;
        switch (port) {
            case "3010": return "http://localhost:3010/users"; // FR
            case "3020": return "http://localhost:3020/users"; // US
            case "3030": return "http://localhost:3030/users"; // CH
            default: return "http://localhost:3020/users"; // fallback
        }
    }

    async function registerUser(event) {
        event.preventDefault();

        const login = document.getElementById('login').value.trim();
        const password = document.getElementById('password').value;
        const confirm_password = document.getElementById('confirm_password').value;
        const role = document.getElementById('role').value;
        const message = document.getElementById('responseMessage');

        if (password !== confirm_password) {
            message.textContent = "<?= $t['error_mismatch'] ?>";
            message.style.color = "red";
            return false;
        }

        const url = getApiUrl();

        try {
            const response = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ login: login, mdp: password, rôle: role })
            });

            const data = await response.json();

            if (response.ok) {
                message.textContent = "<?= $t['success'] ?>";
                message.style.color = "green";
                document.getElementById('registerForm').reset();
                setTimeout(() => window.location.href = "index.php", 2000);
            } else {
                message.textContent = "<?= $t['error_generic'] ?> " + (data.error || "inscription échouée.");
                message.style.color = "red";
            }

        } catch (error) {
            message.textContent = "<?= $t['error_connection'] ?>";
            message.style.color = "red";
        }

        return false;
    }
</script>

</body>
</html>
