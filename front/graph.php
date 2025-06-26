<?php
// Appel API des données historiques
$url = "http://localhost:3002/coronavirus_daily";
$response = file_get_contents($url);
$data = json_decode($response, true);

// Étape 1 : Extraire la liste des pays avec leur première date
$paysInfos = [];
foreach ($data as $entry) {
    $id = $entry['id_pays'];
    $nom = $entry['pays'];
    $date = substr($entry['date'], 0, 10);

    if (!isset($paysInfos[$id])) {
        $paysInfos[$id] = [
            'nom' => $nom,
            'first_date' => $date
        ];
    } elseif ($date < $paysInfos[$id]['first_date']) {
        $paysInfos[$id]['first_date'] = $date;
    }
}

// Étape 2 : Liste déroulante
$countries = [];
foreach ($paysInfos as $id => $info) {
    $countries[$id] = $info['nom'];
}
asort($countries);

// Étape 3 : Sélection pays
$selectedIdPays = $_GET['country'] ?? array_key_first($countries);
$selectedCountryName = $countries[$selectedIdPays] ?? "Inconnu";

// Étape 4 : Dates mensuelles
$start = new DateTime($paysInfos[$selectedIdPays]['first_date']);
$end = new DateTime('2022-05-14');
$dates = [];
while ($start <= $end) {
    $dates[] = $start->format('Y-m-d');
    $start->modify('first day of next month');
}

// Étape 5 : Données historiques
$dataPointsCases = [];
$dataPointsDeaths = [];

foreach ($data as $entry) {
    if ((string)$entry['id_pays'] === (string)$selectedIdPays) {
        $entryDate = substr($entry['date'], 0, 10);
        if (in_array($entryDate, $dates)) {
            $timestamp = strtotime($entryDate) * 1000;
            $dataPointsCases[] = ["x" => $timestamp, "y" => (int)$entry['casActif']];
            $dataPointsDeaths[] = ["x" => $timestamp, "y" => (int)$entry['cumulMortTotaux']];
        }
    }
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8">
    <title>MSPR 6.1</title>
    <link rel="stylesheet" href="style.css">
    <script>
        window.onload = function () {
            const normalColors = ["#4F81BC", "#C0504E"];
            const daltonismColors = ["#0072B2", "#E69F00"];
            let currentColors = [...normalColors];

            const chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                title: { text: "Data COVID pour <?php echo htmlspecialchars($selectedCountryName); ?>" },
                subtitles: [{ text: "Cas actifs, morts, cas cumulés et prédictions IA", fontSize: 18 }],
                axisY: { title: "Nombre de cas" },
                legend: { cursor: "pointer", itemclick: toggleDataSeries },
                toolTip: { shared: true },
                data: [
                    {
                        type: "area",
                        name: "Cas actifs",
                        showInLegend: true,
                        color: currentColors[0],
                        xValueType: "dateTime",
                        xValueFormatString: "MMM YYYY",
                        dataPoints: <?php echo json_encode($dataPointsCases, JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "area",
                        name: "Total de morts",
                        showInLegend: true,
                        color: currentColors[1],
                        xValueType: "dateTime",
                        xValueFormatString: "MMM YYYY",
                        dataPoints: <?php echo json_encode($dataPointsDeaths, JSON_NUMERIC_CHECK); ?>
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
        }
    </script>
</head>
<body>

<header>
    <h2>MSPR 6.1</h2>
</header>

<h1>Évolution des cas actifs et des morts</h1>

<form method="get">
    <label for="country">Choisir un pays :</label>
    <select name="country" id="country" onchange="this.form.submit()">
        <?php foreach ($countries as $id => $name): ?>
            <option value="<?= htmlspecialchars($id) ?>" <?= ($id == $selectedIdPays) ? 'selected' : '' ?>>
                <?= htmlspecialchars($name) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<!-- Mode daltonien -->
<label>
    <input type="checkbox" id="colorblindToggle"> Mode daltonisme
</label>

<!-- Graphique -->
<div id="chartContainer" style="height: 370px; width: 100%; margin-top: 20px;"></div>
<script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>

<div class="button-container">
    <a href="graph2.php"><button>Coronavirus monde</button></a>
    <a href="prediGraph.php"><button>Voir les prédictions de l'IA</button></a>
    <a href="graph3.php"><button>Monkeypox</button></a>
</div>
<div class="button-container">
    <a href="index_co.php"><button>Retour à l'accueil</button></a>
</div>

</body>
</html>
