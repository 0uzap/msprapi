<?php
// Appel API
$url = "http://localhost:3002/coronavirus_daily";
$response = file_get_contents($url);
$data = json_decode($response, true);

// Extraire les pays uniques
$countries = [];
foreach ($data as $entry) {
    $countries[] = $entry['country'];
}
$countries = array_unique($countries);
sort($countries);

// Pays sélectionné
$selectedCountry = $_GET['country'] ?? reset($countries);

// Dates à inclure (1er du mois + 1er/dernière valeur)
$start = new DateTime('2020-02-15');
$end = new DateTime('2022-05-14');
$dates = [$start->format('Y-m-d')];
$current = new DateTime('2020-03-01');
while ($current < $end) {
    $dates[] = $current->format('Y-m-d');
    $current->modify('first day of next month');
}
$dates[] = $end->format('Y-m-d');

// Filtrer les données par pays
$dataPointsCases = [];
$dataPointsDeaths = [];

foreach ($data as $entry) {
    if ($entry['country'] === $selectedCountry) {
        $entryDate = substr($entry['date'], 0, 10);
        if (in_array($entryDate, $dates)) {
            $timestamp = strtotime($entryDate) * 1000;
            $dataPointsCases[] = ["x" => $timestamp, "y" => (int)$entry['active_cases']];
            $dataPointsDeaths[] = ["x" => $timestamp, "y" => (int)$entry['cumulative_total_deaths']];
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
                title: { text: "Data COVID pour <?php echo htmlspecialchars($selectedCountry); ?>" },
                subtitles: [{ text: "Cas actif et morts", fontSize: 18 }],
                axisY: { title: "Cas actif / Morts" },
                legend: { cursor: "pointer", itemclick: toggleDataSeries },
                toolTip: { shared: true },
                data: [
                    {
                        type: "area",
                        name: "Cas actif",
                        showInLegend: true,
                        xValueType: "dateTime",
                        xValueFormatString: "MMM YYYY",
                        dataPoints: <?php echo json_encode($dataPointsCases, JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "area",
                        name: "Total de mort",
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

    <h1>Evolution cas actif - mort</h1>

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
        <a href="graph3.php"><button>Monkeypox</button></a>
    </div>
    
</body>
</html>
