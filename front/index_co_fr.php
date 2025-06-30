<?php
$lang = 'fr';
$t = [
    'title' => 'MSPR 6.1',
    'heading' => 'Accès aux graphiques',
    'ai_predictions' => "Voir les prédictions de l'IA",
    'register' => 'Inscription d\'un utilisateur',
    'logout' => 'Déconnexion',
    'login_msg' => 'Connecté en tant que :'
];
?>
<!DOCTYPE html>
<html lang="fr">
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

<div class="button-container">
    <a href="prediGraph.php"><button><?= $t['ai_predictions'] ?></button></a>
</div>

<script>
    const user = JSON.parse(localStorage.getItem("user"));
    const header = document.getElementById("header");

    if (!user) {
        document.body.innerHTML = "<p style='text-align:center; color:red;'>Veuillez vous connecter pour accéder à cette page.</p><div style='text-align:center;'><a href='form_fr.php'><button>Se connecter</button></a></div>";
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
