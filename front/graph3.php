<?php
// Appel API 
$url = "http://localhost:3002/monkeypox_data";
$response = file_get_contents($url);
$data = json_decode($response, true);

// Extraire les pays uniques
$countries = [];
foreach ($data as $entry) {
    if (isset($entry['location'])) {
        $countries[] = $entry['location'];
    }
}
$countries = array_unique($countries);
sort($countries);

// Pays sélectionné
$selectedCountry = $_GET['country'] ?? reset($countries);

// Dates à inclure (1er du mois + 1er/dernière valeur)
$start = new DateTime('2022-05-01');
$end = new DateTime('2023-05-05');
$dates = [$start->format('Y-m-d')];
$current = new DateTime('2022-06-01');
while ($current < $end) {
    $dates[] = $current->format('Y-m-d');
    $current->modify('first day of next month');
}
$dates[] = $end->format('Y-m-d');

// Filtrer les données par pays
$dataPointsCases = [];
$dataPointsDeaths = [];

foreach ($data as $entry) {
    if ($entry['location'] === $selectedCountry) {
        $entryDate = substr($entry['date'], 0, 10);
        if (in_array($entryDate, $dates)) {
            $timestamp = strtotime($entryDate) * 1000;
            $dataPointsCases[] = ["x" => $timestamp, "y" => (int)$entry['total_cases']];
            $dataPointsDeaths[] = ["x" => $timestamp, "y" => (int)$entry['total_deaths']];
        }
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>MSPR 6.1</title>
    <link rel="stylesheet" href="style.css">
    <script>
        window.onload = function () {
            var chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                title: { text: "Données Monkeypox - <?php echo htmlspecialchars($selectedCountry); ?>" },
                subtitles: [{ text: "Total des cas et décès", fontSize: 18 }],
                axisY: { title: "Nombre de cas / décès" },
                legend: { cursor: "pointer", itemclick: toggleDataSeries },
                toolTip: { shared: true },
                data: [
                    {
                        type: "area",
                        name: "Cas totaux",
                        showInLegend: true,
                        xValueType: "dateTime",
                        xValueFormatString: "MMM YYYY",
                        dataPoints: <?php echo json_encode($dataPointsCases, JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "area",
                        name: "Décès totaux",
                        showInLegend: true,
                        xValueType: "dateTime",
                        xValueFormatString: "MMM YYYY",
                        dataPoints: <?php echo json_encode($dataPointsDeaths, JSON_NUMERIC_CHECK); ?>
                    }
                ]
            });
            chart.render();

            function toggleDataSeries(e) {
                if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                } else {
                    e.dataSeries.visible = true;
                }
                chart.render();
            }
        }
    </script>
</head>
<body>

    <header>
        <h2>MSPR 6.1</h2>
    </header>

    <h1>Evolution Monkeypox</h1>

    <form method="get">
        <label for="country">Choisir un pays :</label>
        <select name="country" id="country" onchange="this.form.submit()">
            <?php foreach ($countries as $country): ?>
                <option value="<?= htmlspecialchars($country) ?>" <?= $selectedCountry === $country ? 'selected' : '' ?>>
                    <?= htmlspecialchars($country) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Graphique -->
    <div id="chartContainer" style="height: 370px; width: 100%; margin-top: 20px;"></div>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>

    <div class="button-container">
        <a href="graph2.php"><button>Coronavirus monde</button></a>
        <a href="index.html"><button>Retour à l'accueil</button></a>
        <a href="graph.php"><button>Coronavirus journalier</button></a>
    </div>

</body>
</html>
