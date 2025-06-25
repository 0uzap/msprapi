<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="style_form.css">
    <script src="script.js" defer></script>
</head>
<body>

<header>
    <h2>MSPR 6.1</h2>
</header>

<form id="registerForm" onsubmit="return registerUser(event)">
    <h2>Inscription</h2>

    <label for="login">Login :</label>
    <input type="text" id="login" name="login" required>

    <label for="password">Mot de passe :</label>
    <input type="password" id="password" name="password" required>
    <button type="button" class="form-button" onclick="togglePassword('password')">Afficher / Cacher</button>

    <label for="confirm_password">Confirmer le mot de passe :</label>
    <input type="password" id="confirm_password" name="confirm_password" required>
    <button type="button" class="form-button" onclick="togglePassword('confirm_password')">Afficher / Cacher</button>

    <label for="role">Rôle :</label>
    <select id="role" name="role" class="form-select" required>
        <option value="utilisateur" selected>Utilisateur</option>
        <option value="admin">Admin</option>
    </select>

    <input type="submit" class="form-button" value="S'inscrire">

    <div class="return-link">
        <a href="index.html"><button type="button" class="return-button">Retour à l'accueil</button></a>
    </div>

    <p id="responseMessage"></p>
</form>

<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        input.type = input.type === "password" ? "text" : "password";
    }

    async function registerUser(event) {
        event.preventDefault();

        const login = document.getElementById('login').value.trim();
        const password = document.getElementById('password').value;
        const confirm_password = document.getElementById('confirm_password').value;
        const role = document.getElementById('role').value;
        const message = document.getElementById('responseMessage');

        if (password !== confirm_password) {
            message.textContent = "Les mots de passe ne correspondent pas.";
            message.style.color = "red";
            return false;
        }

        try {
            const response = await fetch("http://localhost:3002/users", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    login: login,
                    mdp: password,
                    rôle: role
                })
            });

            const data = await response.json();

            if (response.ok) {
                message.textContent = "Inscription réussie ! Redirection...";
                message.style.color = "green";
                document.getElementById('registerForm').reset();
                setTimeout(() => {
                    window.location.href = "index.html";
                }, 2000);
            } else {
                message.textContent = "Erreur : " + (data.error || "inscription échouée.");
                message.style.color = "red";
            }

        } catch (error) {
            message.textContent = "Erreur de connexion à l’API.";
            message.style.color = "red";
        }

        return false;
    }
</script>

</body>
</html>
