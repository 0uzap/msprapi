<?php
// Appel API
$apiUrl = "http://localhost:3002/covid_country";
$response = file_get_contents($apiUrl);
$countries = [];
$data = [];

if ($response !== false) {
    $data = json_decode($response);
    foreach ($data as $item) {
        $countries[] = $item->country_region;
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
    if ($item->country_region === $selectedCountry1) {
        $countryData1 = $item;
    }
    if ($item->country_region === $selectedCountry2) {
        $countryData2 = $item;
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
            theme: "light2",
            title:{
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
                    dataPoints: [
                        { label: "Morts", y: <?php echo $countryData1->deaths ?? 0; ?> },
                        { label: "Soignés", y: <?php echo $countryData1->recovered ?? 0; ?> },
                        { label: "Actifs", y: <?php echo $countryData1->active ?? 0; ?> }
                    ]
                },
                {
                    type: "column",
                    name: "<?php echo $selectedCountry2; ?>",
                    showInLegend: true,
                    dataPoints: [
                        { label: "Morts", y: <?php echo $countryData2->deaths ?? 0; ?> },
                        { label: "Soignés", y: <?php echo $countryData2->recovered ?? 0; ?> },
                        { label: "Actifs", y: <?php echo $countryData2->active ?? 0; ?> }
                    ]
                }
            ]
        });
        chart.render();
    }
    </script>
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
        <p><strong>Cas confirmés <?php echo $selectedCountry1; ?> :</strong> <?php echo $countryData1->confirmed; ?></p>
        <p><strong>Cas confirmés <?php echo $selectedCountry2; ?> :</strong> <?php echo $countryData2->confirmed; ?></p>
    <?php endif; ?>

    <!-- Graphique -->
    <div id="chartContainer" style="height: 400px; width: 100%;"></div>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>

    <div class="button-container">
        <a href="graph.php"><button>Coronavirus journalier</button></a>
        <a href="index.html"><button>Retour à l'accueil</button></a>
        <a href="graph3.php"><button>Monkeypox</button></a>
    </div>

</body>
</html>
