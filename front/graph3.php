<?php
$server = $_GET['server'] ?? 'us';
if (!in_array($server, ['us','fr','ch'])) $server = 'us';

if ($server !== 'us') {
    $errors = [
        'fr' => "Accès refusé : votre serveur ne permet pas d'accéder à ces données.",
        'ch' => "Accès refusé : votre serveur ne permet pas d'accéder à ces données. / "
              . "Zugriff verweigert: Ihr Server erlaubt keinen Zugriff auf diese Daten. / "
              . "Accesso negato: il tuo server non consente l'accesso a questi dati."
    ];
    $msg = $errors[$server] ?? $errors['fr'];
    echo <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title>
<style>body{font-family:sans-serif}.error{color:red;text-align:center;margin-top:50px}</style>
</head><body><p class="error">{$msg}</p></body></html>
HTML;
    exit;
}

// Appel API
$url = "http://localhost:3020/monkeypox_data";
$response = file_get_contents($url);
$data = json_decode($response, true);

// Pays uniques
$countries = [];
foreach ($data as $entry) {
    if (isset($entry['pays'])) $countries[] = $entry['pays'];
}
$countries = array_unique($countries);
sort($countries);

// Pays sélectionné
$selectedCountry = $_GET['country'] ?? reset($countries);

// Dates
$start = new DateTime('2022-05-01');
$end = new DateTime('2023-05-05');
$dates = [$start->format('Y-m-d')];
$current = new DateTime('2022-06-01');
while ($current < $end) {
    $dates[] = $current->format('Y-m-d');
    $current->modify('first day of next month');
}
$dates[] = $end->format('Y-m-d');

// Données filtrées
$dataPointsCases = [];
$dataPointsDeaths = [];
foreach ($data as $entry) {
    if ($entry['pays'] === $selectedCountry) {
        $entryDate = substr($entry['date'], 0, 10);
        if (in_array($entryDate, $dates)) {
            $ts = strtotime($entryDate) * 1000;
            $dataPointsCases[]  = ["x" => $ts, "y" => (int)$entry['nbCasTotaux']];
            $dataPointsDeaths[] = ["x" => $ts, "y" => (int)$entry['nbMortTotaux']];
        }
    }
}

// Résumé vocal
$maxCases = max(array_column($dataPointsCases, 'y')) ?? 0;
$maxDeaths = max(array_column($dataPointsDeaths, 'y')) ?? 0;
$lastCase = end($dataPointsCases)['y'] ?? 0;
$lastDeath = end($dataPointsDeaths)['y'] ?? 0;
$graphSummary = "At the top of the screen, there is one boxe that let you choose the country to analyse."
                . "Reading Mokeypox data for $selectedCountry, the highest number of monkeypox cases was $maxCases and the highest number of deaths was $maxDeaths. "
                . "As of the latest recorded date, there are $lastCase cases and $lastDeath deaths."
                . "At the bottom of the screen, there is 3 buttons that let you access other pages. Data for Coronavirus global, Back to home, and data Coronavirus daily." ;
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MSPR 6.3</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
</head>
<body>

<header><h2>MSPR 6.3</h2></header>
<h1>Monkeypox Evolution</h1>

<!-- Résumé pour lecteur vocal, masqué visuellement -->
<div id="graphSummary" style="position:absolute; left:-9999px;" aria-live="polite">
  <?= htmlspecialchars($graphSummary) ?>
</div>

<form method="get">
    <label for="country">Choose a country:</label>
    <select name="country" id="country" onchange="this.form.submit()">
        <?php foreach ($countries as $country): ?>
            <option value="<?= htmlspecialchars($country) ?>" <?= $selectedCountry === $country ? 'selected' : '' ?>>
                <?= htmlspecialchars($country) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="server" value="<?= $server ?>">
</form>

<!-- Daltonisme -->
<label><input type="checkbox" id="colorblindToggle"> Colorblind mode</label>

<!-- Bouton vocal -->
<button id="readPageBtn" class="accessibility-button" aria-label="Read content aloud">🔊 Voice playback</button>

<!-- Graphique -->
<div id="chartContainer" style="height: 370px; width: 100%; margin-top: 20px;"></div>

<div class="button-container">
    <a href="graph2.php?server=<?= $server ?>"><button>Coronavirus Global</button></a>
    <a href="index_co.php?server=<?= $server ?>"><button>Back to home</button></a>
    <a href="graph.php?server=<?= $server ?>"><button>Coronavirus daily</button></a>
</div>

<script>
window.onload = function () {
    const normalColors = ["#4F81BC", "#C0504E"];
    const daltonismColors = ["#0072B2", "#E69F00"];
    let currentColors = [...normalColors];

    const chart = new CanvasJS.Chart("chartContainer", {
        animationEnabled: true,
        title: { text: "Monkeypox Data - <?= htmlspecialchars($selectedCountry) ?>" },
        subtitles: [{ text: "Total cases and deaths", fontSize: 18 }],
        axisY: { title: "Count" },
        legend: { cursor: "pointer", itemclick: e => {
            e.dataSeries.visible = !(typeof e.dataSeries.visible === "undefined" || e.dataSeries.visible);
            chart.render();
        }},
        toolTip: { shared: true },
        data: [
            {
                type: "area",
                name: "Total Cases",
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

    document.getElementById("colorblindToggle").addEventListener("change", function () {
        const useDaltonism = this.checked;
        currentColors = useDaltonism ? daltonismColors : normalColors;
        chart.options.data[0].color = currentColors[0];
        chart.options.data[1].color = currentColors[1];
        chart.render();
    });
};

// Lecture vocale sur clic
document.getElementById("readPageBtn").addEventListener("click", () => {
    window.speechSynthesis.cancel();
    const summary = document.getElementById("graphSummary").innerText;
    const utterance = new SpeechSynthesisUtterance(summary);
    utterance.lang = "en-US";
    utterance.rate = 1;
    utterance.pitch = 1;
    utterance.volume = 1;
    window.speechSynthesis.speak(utterance);
});
</script>

</body>
</html>
