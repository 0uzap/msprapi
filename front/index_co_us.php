<?php
$lang = 'en';
$t = [
    'title' => 'MSPR 6.1',
    'heading' => 'Graph Access',
    'world' => 'Coronavirus Global',
    'daily' => 'Coronavirus Daily',
    'monkeypox' => 'Monkeypox',
    'register' => 'Register a User',
    'logout' => 'Logout',
    'login_msg' => 'Logged in as:'
];
?>
<!DOCTYPE html>
<html lang="en">
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
    <a href="graph.php"><button><?= $t['daily'] ?></button></a>
    <a href="graph2.php"><button><?= $t['world'] ?></button></a>
    <a href="graph3.php"><button><?= $t['monkeypox'] ?></button></a>
</div>

<script>
    const user = JSON.parse(localStorage.getItem("user"));
    const header = document.getElementById("header");

    if (!user) {
        document.body.innerHTML = "<p style='text-align:center; color:red;'>Please log in to access this page.</p><div style='text-align:center;'><a href='form_us.php'><button>Login</button></a></div>";
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
