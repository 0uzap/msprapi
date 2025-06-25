<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="style_form.css">
</head>
<body>

    <header>
        <h2>MSPR 6.1</h2>
    </header>

    <form onsubmit="return loginUser(event)">
        <h2>Connexion</h2>
        
        <label for="login">Login :</label>
        <input type="text" id="login" name="login" required>

        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>

        <button type="button" class="form-button" onclick="togglePassword()">Afficher / Cacher</button>

        <input type="submit" class="form-button" value="Se connecter">

        <div class="return-link">
            <a href="index.html"><button type="button" class="return-button">Retour à l'accueil</button></a>
        </div>

        <p id="responseMessage"></p>
    </form>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            passwordInput.type = (passwordInput.type === "password") ? "text" : "password";
        }

        async function loginUser(event) {
            event.preventDefault();

            const login = document.getElementById("login").value;
            const password = document.getElementById("password").value;
            const message = document.getElementById("responseMessage");

            try {
                const response = await fetch("http://localhost:3002/users/login", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ login: login, mdp: password })
                });

                const data = await response.json();

                if (response.ok) {
                    localStorage.setItem("user", JSON.stringify(data));
                    window.location.href = "index_co.php";
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
