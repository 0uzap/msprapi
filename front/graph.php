<?php
// 1) Mémorisation du serveur via GET (us, fr, ch)
$server = $_GET['server'] ?? 'us';
if (!in_array($server, ['us','fr','ch'])) {
    $server = 'us';
}

// 2) Si on n’est pas sur US, afficher le message d’erreur et sortir
if ($server !== 'us') {
    $errors = [
        'fr' => "Access denied: Your server does not allow access to this data.",
        'ch' => "Access denied: Your server does not allow access to this data. / "
               . "Zugriff verweigert: Ihr Server erlaubt keinen Zugriff auf diese Daten. / "
               . "Accesso negato: il tuo server non consente l'accesso a questi dati."
    ];
    $msg = $errors[$server] ?? $errors['fr'];
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Error</title>
  <style>
    body { font-family: sans-serif; }
    .error { color: red; text-align: center; margin-top: 50px; }
  </style>
</head>
<body>
  <p class="error">{$msg}</p>
</body>
</html>
HTML;
    exit;
}

// 3) Serveur US : appel API des données historiques
$url = "http://localhost:3020/coronavirus_daily";
$response = file_get_contents($url);
$data = json_decode($response, true);

// 4) Extraire la liste des pays avec leur première date
$paysInfos = [];
foreach ($data as $entry) {
    $id   = $entry['id_pays'];
    $date = substr($entry['date'], 0, 10);
    if (!isset($paysInfos[$id]) || $date < $paysInfos[$id]['first_date']) {
        $paysInfos[$id] = [
            'nom'        => $entry['pays'],
            'first_date' => $date
        ];
    }
}

$countries = [];
foreach ($paysInfos as $id => $info) {
    $countries[$id] = $info['nom'];
}
asort($countries);

// Étape 5 : Sélection pays
$selectedIdPays = $_GET['country'] ?? array_key_first($countries);
$selectedCountryName = $countries[$selectedIdPays] ?? "Inconnu";

// Étape 6 : Générer dates
$start = new DateTime($paysInfos[$selectedIdPays]['first_date']);
$end   = new DateTime('2022-05-14');
$dates = [];
while ($start <= $end) {
    $dates[] = $start->format('Y-m-d');
    $start->modify('first day of next month');
}

// Étape 7 : Données graphiques
$dataPointsCases  = [];
$dataPointsDeaths = [];
foreach ($data as $entry) {
    if ((string)$entry['id_pays'] === (string)$selectedIdPays) {
        $d = substr($entry['date'], 0, 10);
        if (in_array($d, $dates, true)) {
            $ts = strtotime($d) * 1000;
            $dataPointsCases[]  = ['x' => $ts, 'y' => (int)$entry['casActif']];
            $dataPointsDeaths[] = ['x' => $ts, 'y' => (int)$entry['cumulMortTotaux']];
        }
    }
}

// Étape 8 : Résumé pour lecture vocale
$maxCases = max(array_column($dataPointsCases, 'y'));
$maxDeaths = max(array_column($dataPointsDeaths, 'y'));
$lastCase = end($dataPointsCases)['y'] ?? 0;
$lastDeath = end($dataPointsDeaths)['y'] ?? 0;

$graphSummary = "At the top of the screen, there is one boxe that let you choose the country to analyse."
              . "For $selectedCountryName, the highest number of active COVID cases was $maxCases and the highest number of deaths was $maxDeaths. "
              . "As of the latest recorded date, there are $lastCase active cases and $lastDeath cumulative deaths."
              . "At the bottom of the screen, there is 3 buttons that let you access other pages. Data for Coronavirus daily, the prediction of the AI for Coronavirus, and data for Monkeypox. There is also a button to go back to the home page.";
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MSPR 6.3</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<header>
  <h2>MSPR 6.3</h2>
</header>

<h1>Evolution of Active Cases and Deaths</h1>

<!-- Résumé pour lecture vocale, masqué visuellement -->
<div id="graphSummary" style="position: absolute; left: -9999px;" aria-live="polite">
  <?= htmlspecialchars($graphSummary) ?>
</div>

<form method="get">
  <label for="country">Choose a country:</label>
  <select name="country" id="country" onchange="this.form.submit()">
    <?php foreach ($countries as $id => $name): ?>
      <option value="<?= htmlspecialchars($id) ?>"
        <?= $id == $selectedIdPays ? 'selected' : '' ?>>
        <?= htmlspecialchars($name) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <input type="hidden" name="server" value="<?= $server ?>">
</form>

<label>
  <input type="checkbox" id="colorblindToggle"> Colorblind mode
</label>

<!-- Bouton lecture vocale -->
<button id="readPageBtn" class="accessibility-button" aria-label="Read graph summary aloud">🔊 Voice playback</button>

<div id="chartContainer" style="height: 370px; width: 100%; margin-top: 20px;"></div>

<script>
window.onload = function () {
  const normalColors    = ["#4F81BC", "#C0504E"];
  const daltonismColors = ["#0072B2", "#E69F00"];
  let currentColors     = [...normalColors];

  const chart = new CanvasJS.Chart("chartContainer", {
    animationEnabled: true,
    title: { text: "COVID Data for <?= htmlspecialchars($selectedCountryName) ?>" },
    subtitles: [{ text: "Active cases and cumulative deaths", fontSize: 18 }],
    axisY: { title: "Case Count" },
    legend: { cursor: "pointer", itemclick: toggleDataSeries },
    toolTip: { shared: true },
    data: [
      {
        type: "area",
        name: "Active Cases",
        showInLegend: true,
        color: currentColors[0],
        xValueType: "dateTime",
        xValueFormatString: "MMM YYYY",
        dataPoints: <?= json_encode($dataPointsCases, JSON_NUMERIC_CHECK); ?>
      },
      {
        type: "area",
        name: "Total Deaths",
        showInLegend: true,
        color: currentColors[1],
        xValueType: "dateTime",
        xValueFormatString: "MMM YYYY",
        dataPoints: <?= json_encode($dataPointsDeaths, JSON_NUMERIC_CHECK); ?>
      }
    ]
  });
  chart.render();

  function toggleDataSeries(e) {
    e.dataSeries.visible = !(typeof e.dataSeries.visible === "undefined" || e.dataSeries.visible);
    chart.render();
  }

  document.getElementById("colorblindToggle").addEventListener("change", function () {
    const useDaltonism = this.checked;
    currentColors = useDaltonism ? daltonismColors : normalColors;
    chart.options.data[0].color = currentColors[0];
    chart.options.data[1].color = currentColors[1];
    chart.render();
  });
};
</script>

<div class="button-container">
  <a href="graph2.php?server=<?= $server ?>"><button>Coronavirus Global</button></a>
  <a href="prediGraph.php?server=<?= $server ?>"><button>See AI predictions</button></a>
  <a href="graph3.php?server=<?= $server ?>"><button>Monkeypox</button></a>
</div>
<div class="button-container">
  <a href="index_co.php?server=<?= $server ?>"><button>Back to home</button></a>
</div>

<!-- Lecture vocale uniquement du résumé -->
<script>
document.getElementById("readPageBtn").addEventListener("click", () => {
    window.speechSynthesis.cancel();
    const summary = document.getElementById("graphSummary").innerText;
    const utterance = new SpeechSynthesisUtterance(summary);
    utterance.lang = "en-US"; // Adapter selon le serveur si besoin
    utterance.rate = 1;
    utterance.pitch = 1;
    utterance.volume = 1;
    window.speechSynthesis.speak(utterance);
});
</script>

</body>
</html>
