<?php
// 1) Lecture du serveur “mémorisé” via GET (fallback us)
$server = $_GET['server'] ?? 'us';
if (! in_array($server, ['us','fr','ch'])) {
    $server = 'us';
}

// 2) Langue pour CH, sinon par défaut en/fr
$lang = $server === 'ch'
      ? ($_GET['lang'] ?? 'fr')
      : ($server === 'us' ? 'en' : 'fr');
if ($server === 'ch' && ! in_array($lang, ['fr','de','it'])) {
    $lang = 'fr';
}

// 3) URLs d’API selon serveur
$apiUrls = [
    'us' => 'http://localhost:3020/covid_country',
    'fr' => 'http://localhost:3010/covid_country',
    'ch' => 'http://localhost:3030/covid_country',
];
$apiUrl = $apiUrls[$server];

// 4) Erreur si pas US
if ($server !== 'us') {
    $errors = [
        'fr' => "Accès refusé : votre serveur ne permet pas d'accéder à ces données.",
        'ch' => "Accès refusé : votre serveur ne permet pas d'accéder à ces données. / "
              . "Zugriff verweigert: Ihr Server erlaubt keinen Zugriff auf diese Daten. / "
              . "Accesso negato: il tuo server non consente l'accesso a questi dati."
    ];
    $msg = $errors[$server] ?? $errors['fr'];
    echo <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Erreur</title>
<style>body{font-family:sans-serif}.error{color:red;text-align:center;margin-top:50px}</style>
</head><body>
  <p class="error">{$msg}</p>
</body></html>
HTML;
    exit;
}

// 5) Traductions FR/EN pour cette page
$pageTexts = [
  'fr'=>[
    'heading'=>'Comparaison COVID-19 entre deux pays',
    'label1'=>'Pays 1 :',
    'label2'=>'Pays 2 :',
    'submit'=>'Comparer',
    'confirmed'=>'Cas confirmés',
    'colorblind'=>'Mode daltonisme',
    'nav1'=>'Coronavirus journalier',
    'nav2'=>'Retour à l’accueil',
    'nav3'=>'Monkeypox'
  ],
  'en'=>[
    'heading'=>'COVID-19 Comparison Between Two Countries',
    'label1'=>'Country 1:',
    'label2'=>'Country 2:',
    'submit'=>'Compare',
    'confirmed'=>'Confirmed cases',
    'colorblind'=>'Colorblind mode',
    'nav1'=>'Coronavirus daily',
    'nav2'=>'Back to home',
    'nav3'=>'Monkeypox'
  ]
];
$pt = $pageTexts[$lang];

// 6) Appel API
$response = @file_get_contents($apiUrl);
$countries = $data = [];
if ($response !== false) {
    $data = json_decode($response);
    foreach ($data as $item) {
        $countries[] = $item->pays;
    }
} else {
    echo "Erreur lors de l'appel à l'API.";
}

// 7) Sélections
$selectedCountry1 = $_POST['country1'] ?? 'Afghanistan';
$selectedCountry2 = $_POST['country2'] ?? 'Albania';
$countryData1 = $countryData2 = null;
foreach ($data as $item) {
    if ($item->pays === $selectedCountry1) $countryData1 = $item;
    if ($item->pays === $selectedCountry2) $countryData2 = $item;
}

// 8) Préparer query string pour navigation
$qs = "server={$server}";
if ($server==='ch') $qs .= "&lang={$lang}";
?>
<!DOCTYPE HTML>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>MSPR 6.1</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
</head>
<body>
    <header><h2>MSPR 6.1</h2></header>
    <h1><?= $pt['heading'] ?></h1>

    <!-- Formulaire de sélection -->
    <form method="post" style="margin-bottom:20px;">
        <input type="hidden" name="server" value="<?= $server ?>">
        <?php if ($server==='ch'): ?>
            <input type="hidden" name="lang" value="<?= $lang ?>">
        <?php endif; ?>

        <label for="country1"><?= $pt['label1'] ?></label>
        <select name="country1" id="country1">
            <?php foreach ($countries as $c): ?>
                <option value="<?= $c ?>" <?= $c === $selectedCountry1 ? 'selected' : '' ?>>
                    <?= $c ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="country2"><?= $pt['label2'] ?></label>
        <select name="country2" id="country2">
            <?php foreach ($countries as $c): ?>
                <option value="<?= $c ?>" <?= $c === $selectedCountry2 ? 'selected' : '' ?>>
                    <?= $c ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit"><?= $pt['submit'] ?></button>
    </form>

    <!-- Cas confirmés -->
    <?php if ($countryData1 && $countryData2): ?>
        <p><strong><?= $pt['confirmed'] ?> <?= $selectedCountry1 ?> :</strong> <?= $countryData1->nbCas ?></p>
        <p><strong><?= $pt['confirmed'] ?> <?= $selectedCountry2 ?> :</strong> <?= $countryData2->nbCas ?></p>
    <?php endif; ?>

    <!-- Mode daltonisme -->
    <label><input type="checkbox" id="colorblindToggle"> <?= $pt['colorblind'] ?></label>

    <!-- Graphique -->
    <div id="chartContainer" style="height: 400px; width: 100%;"></div>

    <script>
    window.onload = function () {
        const normalColors    = ["#6D78AD", "#51CDA0"];
        const daltonismColors = ["#0072B2", "#E69F00"];

        const chart = new CanvasJS.Chart("chartContainer", {
            animationEnabled: true, theme:"light2",
            title:{ text: "<?= $selectedCountry1 ?> vs <?= $selectedCountry2 ?>" },
            axisY:{ title: "Nombre de cas" },
            toolTip:{ shared: true },
            legend:{ cursor: "pointer", itemclick: e => {
                e.dataSeries.visible = !e.dataSeries.visible; chart.render();
            }},
            data:[
                {
                  type:"column", name:"<?= $selectedCountry1 ?>",
                  showInLegend:true, color:normalColors[0],
                  dataPoints:[
                    { label:"Morts",   y:<?= $countryData1->nbMort   ?? 0 ?> },
                    { label:"Soignés", y:<?= $countryData1->nbSoigne ?? 0 ?> },
                    { label:"Actifs",  y:<?= $countryData1->nbActif  ?? 0 ?> }
                  ]
                },
                {
                  type:"column", name:"<?= $selectedCountry2 ?>",
                  showInLegend:true, color:normalColors[1],
                  dataPoints:[
                    { label:"Morts",   y:<?= $countryData2->nbMort   ?? 0 ?> },
                    { label:"Soignés", y:<?= $countryData2->nbSoigne ?? 0 ?> },
                    { label:"Actifs",  y:<?= $countryData2->nbActif  ?? 0 ?> }
                  ]
                }
            ]
        });
        chart.render();

        document.getElementById("colorblindToggle").addEventListener("change", function() {
            const useDalton = this.checked;
            chart.options.data[0].color = useDalton ? daltonismColors[0] : normalColors[0];
            chart.options.data[1].color = useDalton ? daltonismColors[1] : normalColors[1];
            chart.render();
        });
    };
    </script>

    <!-- Liens de navigation -->
    <div class="button-container">
        <a href="graph.php?<?= $qs ?>"><button><?= $pt['nav1'] ?></button></a>
        <a href="index_co.php?<?= $qs ?>"><button><?= $pt['nav2'] ?></button></a>
        <a href="graph3.php?<?= $qs ?>"><button><?= $pt['nav3'] ?></button></a>
    </div>
</body>
</html>
