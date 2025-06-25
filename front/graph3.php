<?php
// Appel API 
$url = "http://localhost:3002/monkeypox_data";
$response = file_get_contents($url);
$data = json_decode($response, true);

// Extraire les pays uniques
$countries = [];
foreach ($data as $entry) {
    if (isset($entry['pays'])) {
        $countries[] = $entry['pays'];
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
    if ($entry['pays'] === $selectedCountry) {
        $entryDate = substr($entry['date'], 0, 10);
        if (in_array($entryDate, $dates)) {
            $timestamp = strtotime($entryDate) * 1000;
            $dataPointsCases[] = ["x" => $timestamp, "y" => (int)$entry['nbCasTotaux']];
            $dataPointsDeaths[] = ["x" => $timestamp, "y" => (int)$entry['nbMortTotaux']];
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
            const normalColors = ["#4F81BC", "#C0504E"];
            const daltonismColors = ["#0072B2", "#E69F00"];
            let currentColors = [...normalColors];

            const chart = new CanvasJS.Chart("chartContainer", {
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
                        color: currentColors[0],
                        xValueType: "dateTime",
                        xValueFormatString: "MMM YYYY",
                        dataPoints: <?php echo json_encode($dataPointsCases, JSON_NUMERIC_CHECK); ?>
                    },
                    {
                        type: "area",
                        name: "Décès totaux",
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

    <!-- Case à cocher daltonisme -->
    <label>
        <input type="checkbox" id="colorblindToggle"> Mode daltonisme
    </label>

    <!-- Graphique -->
    <div id="chartContainer" style="height: 370px; width: 100%; margin-top: 20px;"></div>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>

    <div class="button-container">
        <a href="graph2.php"><button>Coronavirus monde</button></a>
        <a href="index_co.php"><button>Retour à l'accueil</button></a>
        <a href="graph.php"><button>Coronavirus journalier</button></a>
    </div>

</body>
</html>