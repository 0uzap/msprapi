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

// Étape 5 : Données historiques cumulées
$dataPointsCumul = [];
foreach ($data as $entry) {
    if ((string)$entry['id_pays'] === (string)$selectedIdPays) {
        $entryDate = substr($entry['date'], 0, 10);
        if (in_array($entryDate, $dates)) {
            $timestamp = strtotime($entryDate) * 1000;
            $dataPointsCumul[] = ["x" => $timestamp, "y" => (int)$entry['cumulCasTotaux']];
        }
    }
}

// Étape 6 : Prédictions IA en POST
$dataPointsPredicted = [];
$features = [16.0, 5.0, 15.0, 0.0, 0.0, 1];
$token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJhZG1pbiIsImV4cCI6MTc1MDk0NzY1OX0.9IEn4PIPsLfj08jKTgKIbnWb_T6hWpZ6XiWK84c7MgA";

$postData = [
    "features" => $features,
    "date" => "2022-05-14",
    "token" => $token
];

$ch = curl_init("http://localhost:8000/predict");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$predictionResponse = curl_exec($ch);
curl_close($ch);

$predictionData = json_decode($predictionResponse, true);
$lastDate = new DateTime('2022-05-14');
$lastValue = end($dataPointsCumul)['y'] ?? 100000;

// Point d’ancrage
$dataPointsPredicted[] = [
    "x" => $lastDate->getTimestamp() * 1000,
    "y" => $lastValue
];

// Ajout des prédictions (3 points)
if (isset($predictionData['predictions'][0])) {
    foreach ($predictionData['predictions'][0] as $predicted) {
        $lastDate->modify('first day of next month');
        $dataPointsPredicted[] = [
            "x" => $lastDate->getTimestamp() * 1000,
            "y" => round($predicted)
        ];
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8">
    <title>MSPR 6.1 – Prédictions IA</title>
    <link rel="stylesheet" href="style.css">
    <script>
        window.onload = function () {
            const normalColors = ["#9B59B6", "#009E73"];
            const daltonismColors = ["#CC79A7", "#2ECC71"];
            let currentColors = [...normalColors];

            const chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                title: { text: "Prédictions COVID – <?php echo htmlspecialchars($selectedCountryName); ?>" },
                subtitles: [{ text: "Cas cumulés & Prédictions IA", fontSize: 18 }],
                axisY: { title: "Nombre de cas" },
                legend: { cursor: "pointer", itemclick: toggleDataSeries },
                toolTip: { shared: true },
                data: [
                    {
                        type: "area",
                        name: "Cas cumulés",
                        showInLegend: true,
                        color: currentColors[1],
                        xValueType: "dateTime",
                        xValueFormatString: "MMM YYYY",
                        dataPoints: <?php echo json_encode($dataPointsCumul, JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "area",
                        name: "Prédiction IA",
                        showInLegend: true,
                        color: currentColors[0],
                        xValueType: "dateTime",
                        xValueFormatString: "MMM YYYY",
                        dataPoints: <?php echo json_encode($dataPointsPredicted, JSON_NUMERIC_CHECK); ?>
                    }
                ]
            });

            chart.render();

            function toggleDataSeries(e) {
                e.dataSeries.visible = !(typeof e.dataSeries.visible === "undefined" || e.dataSeries.visible);
                chart.render();
            }

            document.getElementById("colorblindToggle").addEventListener("change", function () {
                currentColors = this.checked ? daltonismColors : normalColors;
                chart.options.data[0].color = currentColors[1];
                chart.options.data[1].color = currentColors[0];
                chart.render();
            });
        };
    </script>
</head>
<body>

<header>
    <h2>MSPR 6.1</h2>
</header>

<h1>Prédictions IA – Cas cumulés</h1>

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

<!-- Daltonisme -->
<label><input type="checkbox" id="colorblindToggle"> Mode daltonisme</label>

<!-- Graphique -->
<div id="chartContainer" style="height: 400px; width: 100%; margin-top: 20px;"></div>
<script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>

<!-- Navigation -->
<div class="button-container">
    <a href="graph.php"><button>Retour données historiques</button></a>
    <a href="index_co.php"><button>Accueil</button></a>
</div>

</body>
</html>