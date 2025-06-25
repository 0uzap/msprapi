<?php
// Appel API
$apiUrl = "http://localhost:3002/covid_country";
$response = file_get_contents($apiUrl);
$countries = [];
$data = [];

if ($response !== false) {
    $data = json_decode($response);
    foreach ($data as $item) {
        $countries[] = $item->pays;
    }
} else {
    echo "Erreur lors de l'appel à l'API.";
}

// Pays sélectionnés (par défaut : Afghanistan et Albania)
$selectedCountry1 = $_POST['country1'] ?? 'Afghanistan';
$selectedCountry2 = $_POST['country2'] ?? 'Albania';

$countryData1 = null;
$countryData2 = null;

foreach ($data as $item) {
    if ($item->pays === $selectedCountry1) {
        $countryData1 = $item;
    }
    if ($item->pays === $selectedCountry2) {
        $countryData2 = $item;
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>MSPR 6.1</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
</head>
<body>
    <header>
        <h2>MSPR 6.1</h2>
    </header>
    <h1>Comparaison COVID-19 entre deux pays</h1>

    <!-- Formulaire de sélection -->
    <form method="post">
        <label for="country1">Pays 1 :</label>
        <select name="country1" id="country1">
            <?php foreach ($countries as $country): ?>
                <option value="<?= $country ?>" <?= ($country === $selectedCountry1) ? 'selected' : '' ?>>
                    <?= $country ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="country2">Pays 2 :</label>
        <select name="country2" id="country2">
            <?php foreach ($countries as $country): ?>
                <option value="<?= $country ?>" <?= ($country === $selectedCountry2) ? 'selected' : '' ?>>
                    <?= $country ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Comparer</button>
    </form>

    <!-- Cas confirmés -->
    <?php if ($countryData1 && $countryData2): ?>
        <p><strong>Cas confirmés <?= $selectedCountry1 ?> :</strong> <?= $countryData1->nbCas ?></p>
        <p><strong>Cas confirmés <?= $selectedCountry2 ?> :</strong> <?= $countryData2->nbCas ?></p>
    <?php endif; ?>

    <!-- Case à cocher Mode Daltonisme -->
    <label>
        <input type="checkbox" id="colorblindToggle"> Mode daltonisme
    </label>

    <!-- Graphique -->
    <div id="chartContainer" style="height: 400px; width: 100%;"></div>

    <!-- Script graphique + Daltonisme -->
    <script>
    window.onload = function () {
        const normalColors = ["#6D78AD", "#51CDA0"];
        const daltonismColors = ["#0072B2", "#E69F00"];

        const chartOptions = {
            animationEnabled: true,
            theme: "light2",
            title: {
                text: "Comparaison COVID-19 : <?php echo $selectedCountry1 . ' vs ' . $selectedCountry2; ?>"
            },
            axisY: {
                title: "Nombre de cas"
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: function (e) {
                    e.dataSeries.visible = !(typeof e.dataSeries.visible === "undefined" || e.dataSeries.visible);
                    chart.render();
                }
            },
            data: [
                {
                    type: "column",
                    name: "<?php echo $selectedCountry1; ?>",
                    showInLegend: true,
                    color: normalColors[0],
                    dataPoints: [
                        { label: "Morts", y: <?php echo $countryData1->nbMort ?? 0; ?> },
                        { label: "Soignés", y: <?php echo $countryData1->nbSoigne ?? 0; ?> },
                        { label: "Actifs", y: <?php echo $countryData1->nbActif ?? 0; ?> }
                    ]
                },
                {
                    type: "column",
                    name: "<?php echo $selectedCountry2; ?>",
                    showInLegend: true,
                    color: normalColors[1],
                    dataPoints: [
                        { label: "Morts", y: <?php echo $countryData2->nbMort ?? 0; ?> },
                        { label: "Soignés", y: <?php echo $countryData2->nbSoigne ?? 0; ?> },
                        { label: "Actifs", y: <?php echo $countryData2->nbActif ?? 0; ?> }
                    ]
                }
            ]
        };

        const chart = new CanvasJS.Chart("chartContainer", chartOptions);
        chart.render();

        // Changement de couleurs en mode daltonisme
        document.getElementById("colorblindToggle").addEventListener("change", function () {
            const useDaltonism = this.checked;
            chart.options.data[0].color = useDaltonism ? daltonismColors[0] : normalColors[0];
            chart.options.data[1].color = useDaltonism ? daltonismColors[1] : normalColors[1];
            chart.render();
        });
    }
    </script>

    <!-- Liens de navigation -->
    <div class="button-container">
        <a href="graph.php"><button>Coronavirus journalier</button></a>
        <a href="index_co.php"><button>Retour à l'accueil</button></a>
        <a href="graph3.php"><button>Monkeypox</button></a>
    </div>
</body>
</html>
