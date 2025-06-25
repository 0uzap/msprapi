<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MSPR 6.1</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header id="header">
        <h2>MSPR 6.1</h2>
    </header>

    <h1>Accès aux graphiques</h1>

    <div class="button-container">
        <a href="graph2.php"><button>Coronavirus monde</button></a>
        <a href="graph.php"><button>Coronavirus journalier</button></a>
        <a href="graph3.php"><button>Monkeypox</button></a>
    </div>

    <script>
        const user = JSON.parse(localStorage.getItem("user"));
        const header = document.getElementById("header");

        if (!user) {
            // Redirection si l'utilisateur n'est pas connecté
            document.body.innerHTML = "<p style='text-align:center; color:red;'>Veuillez vous connecter pour accéder à cette page.</p><div style='text-align:center;'><a href='form.php'><button>Se connecter</button></a></div>";
        } else {
            const info = document.createElement("p");
            info.textContent = `Connecté en tant que : ${user.login}`;
            info.style.marginTop = "10px";
            info.style.color = "green";
            header.appendChild(info);

            const logoutBtn = document.createElement("button");
            logoutBtn.textContent = "Déconnexion";
            logoutBtn.className = "form-button";
            logoutBtn.onclick = () => {
                localStorage.removeItem("user");
                window.location.href = "index.html";
            };
            header.appendChild(logoutBtn);

            if (user.rôle === "admin") {
                const adminBtn = document.createElement("button");
                adminBtn.textContent = "Inscription d'un utilisateur";
                adminBtn.className = "form-button";
                adminBtn.onclick = () => window.location.href = "inscription.php";
                header.appendChild(adminBtn);
            }
        }
    </script>

</body>
</html>
