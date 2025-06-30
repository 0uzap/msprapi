<?php
// Définir la langue par défaut
$lang = $_GET['lang'] ?? 'fr';

// Dictionnaire multilingue
$texts = [
    'fr' => [
        'title' => 'MSPR 6.1',
        'heading' => 'Veuillez choisir votre serveur',
        'description' => "Pour accéder aux outils de datavis, vous devez disposer d'un compte utilisateur sur le serveur choisi",
        'button' => 'Serveur US',
        'button2' => 'Serveur FR',
        'button3' => 'Serveur CH',
        'language' => 'Langue'
    ],
    'en' => [
        'title' => 'MSPR 6.1',
        'heading' => 'Please choose your server',
        'description' => 'To access the datavis tools, you must have a user account on the selected server',
        'button' => 'US server',
        'button2' => 'FR server',
        'button3' => 'CH server',
        'language' => 'Language'
    ],
    'de' => [
        'title' => 'MSPR 6.1',
        'heading' => 'Bitte wählen Sie Ihren Server',
        'description' => 'Um auf die Datavisualisierungs-Tools zuzugreifen, benötigen Sie ein Benutzerkonto auf dem ausgewählten Server',
        'button' => 'US server',
        'button2' => 'FR server',
        'button3' => 'CH server',
        'language' => 'Sprache'
    ],
    'it' => [
        'title' => 'MSPR 6.1',
        'heading' => 'Seleziona il tuo server',
        'description' => 'Per accedere agli strumenti di datavis è necessario un account utente sul server selezionato',
        'button' => 'US server',
        'button2' => 'FR server',
        'button3' => 'CH server',
        'language' => 'Lingua'
    ],
];

// Si la langue n'existe pas, revenir au français
if (!array_key_exists($lang, $texts)) {
    $lang = 'fr';
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $texts[$lang]['title'] ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2><?= $texts[$lang]['title'] ?></h2>
</header>

<h1><?= $texts[$lang]['heading'] ?></h1>
<p><?= $texts[$lang]['description'] ?></p>

<!-- Sélecteur de langue -->
<form method="get" style="margin-bottom: 20px;">
    <label for="lang"><?= $texts[$lang]['language'] ?> :</label>
    <select name="lang" id="lang" onchange="this.form.submit()">
        <option value="fr" <?= $lang === 'fr' ? 'selected' : '' ?>>Français</option>
        <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>English</option>
        <option value="de" <?= $lang === 'de' ? 'selected' : '' ?>>Deutsch</option>
        <option value="it" <?= $lang === 'it' ? 'selected' : '' ?>>Italiano</option>
    </select>
</form>

<div class="button-container">
    <a href="form.php?server=us"><button><?= $texts[$lang]['button'] ?></button></a>
    <a href="form.php?server=fr"><button><?= $texts[$lang]['button2'] ?></button></a>
    <a href="form.php?server=ch"><button><?= $texts[$lang]['button3'] ?></button></a>
</div>

</body>
</html>
