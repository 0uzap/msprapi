<?php
// 1) Mémorisation du serveur et détection langue
$server = $_GET['server'] ?? 'us';
if (! in_array($server, ['us','fr','ch'])) {
    $server = 'us';
}
$lang = $_GET['lang'] 
      ?? ($server === 'ch' ? 'fr' 
         : ($server === 'us' ? 'en' : 'fr'));
if ($server === 'ch' && ! in_array($lang, ['fr','de','it'])) {
    $lang = 'fr';
}

// 3) Texte multilingue
$texts = [
  'fr'=>[
    'title'=>'MSPR 6.3 – Prédictions IA',
    'heading'=>'Prédictions IA – Cas cumulés',
    'select'=>'Choisir un pays :',
    'colorblind'=>'Mode daltonisme',
    'back_data'=>'Retour données historiques',
    'home'=>'Accueil'
  ],
  'en'=>[
    'title'=>'MSPR 6.3 – AI Predictions',
    'heading'=>'AI Predictions – Cumulative Cases',
    'select'=>'Choose a country:',
    'colorblind'=>'Colorblind mode',
    'back_data'=>'Back to historical data',
    'home'=>'Home'
  ],
  'de'=>[
    'title'=>'MSPR 6.3 – KI-Vorhersagen',
    'heading'=>'KI-Vorhersagen – Kumulative Fälle',
    'select'=>'Land wählen:',
    'colorblind'=>'Farbenblindmodus',
    'back_data'=>'Zurück zu historischen Daten',
    'home'=>'Startseite'
  ],
  'it'=>[
    'title'=>'MSPR 6.3 – Previsioni IA',
    'heading'=>'Previsioni IA – Casi cumulativi',
    'select'=>'Scegli un paese:',
    'colorblind'=>'Modalità daltonico',
    'back_data'=>'Torna ai dati storici',
    'home'=>'Home'
  ]
];
$t = $texts[$lang] ?? $texts['fr'];

// 4) Appel API + traitement inchangé
$url = "http://" . [
    'us'=>'localhost:3020',
    'fr'=>'localhost:3020',
    'ch'=>'localhost:3020'
][$server] . "/coronavirus_daily";

$response = file_get_contents($url);
$data = json_decode($response, true);

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

// Étape 5 : Données historiques cumulées et extraction du 2022-05-14
$dataPointsCumul = [];
$selectedFeatureValues = null;

foreach ($data as $entry) {
    if ((string)$entry['id_pays'] === (string)$selectedIdPays) {
        $entryDate = substr($entry['date'], 0, 10);
        
        if ($entryDate === '2022-05-14') {
            // On stocke les features ici
            $selectedFeatureValues = [
                (float) $entry['cumulCasTotaux'] ?? 0,
                (float) $entry['nouveauCasJournalier'] ?? 0,
                (float) $entry['casActif'] ?? 0,
                (float) $entry['cumulMortTotaux'] ?? 0,
                (float) $entry['nouvelleMortJournaliere'] ?? 0,
                (int) $entry['idContinent'] ?? 1  // fallback
            ];
        }

        if (in_array($entryDate, $dates)) {
            $timestamp = strtotime($entryDate) * 1000;
            $dataPointsCumul[] = ["x" => $timestamp, "y" => (int)$entry['cumulCasTotaux']];
        }
    }
}

$dataPointsPredicted = [];
$features = $selectedFeatureValues ?? [100000,2000,50000,100,10,1];
$postData = ["features"=>$features,"date"=>"2022-05-14"];
$ch = curl_init("http://localhost:8000/predict");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
$preds = json_decode(curl_exec($ch), true);
curl_close($ch);

$lastDate  = new DateTime('2022-05-14');
$lastValue = end($dataPointsCumul)['y'] ?? 100000;
$dataPointsPredicted[] = ['x'=>$lastDate->getTimestamp()*1000,'y'=>$lastValue];
if (isset($preds['predictions'][0])) {
    foreach ($preds['predictions'][0] as $p) {
        $lastDate->modify('first day of next month');
        $dataPointsPredicted[] = ['x'=>$lastDate->getTimestamp()*1000,'y'=>round($p)];
    }
}
?>
<!DOCTYPE HTML>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8">
  <title><?= $t['title'] ?></title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>
  window.onload=function(){
    const normal=["#9B59B6","#009E73"],dalton=["#CC79A7","#2ECC71"];
    let cur=[...normal];
    const chart=new CanvasJS.Chart("chartContainer",{
      animationEnabled:true,
      title:{text:"Prédictions COVID – <?= addslashes($selectedCountryName) ?>"},
      subtitles:[{text:"Cas cumulés & Prédictions IA",fontSize:18}],
      axisY:{title:"Nombre de cas"},
      legend:{cursor:"pointer",itemclick:e=>{e.dataSeries.visible=!e.dataSeries.visible;chart.render();}},
      toolTip:{shared:true},
      data:[
        {type:"area",name:"Cas cumulés",showInLegend:true,color:cur[1],
         xValueType:"dateTime",xValueFormatString:"MMM YYYY",
         dataPoints:<?= json_encode($dataPointsCumul,JSON_NUMERIC_CHECK) ?>},
        {type:"area",name:"Prédiction IA",showInLegend:true,color:cur[0],
         xValueType:"dateTime",xValueFormatString:"MMM YYYY",
         dataPoints:<?= json_encode($dataPointsPredicted,JSON_NUMERIC_CHECK) ?>}
      ]
    });
    chart.render();
    document.getElementById("colorblindToggle")
      .addEventListener("change",function(){
        cur=this.checked?dalton:normal;
        chart.options.data[0].color=cur[1];
        chart.options.data[1].color=cur[0];
        chart.render();
      });
  };
  </script>
</head>
<body>
<header><h2>MSPR 6.3</h2></header>
<h1><?= $t['heading'] ?></h1>

<?php if($server==='ch'): ?>
<form method="get" style="margin-bottom:20px;">
  <input type="hidden" name="server" value="ch">
  <label for="lang">Langue :</label>
  <select name="lang" id="lang" onchange="this.form.submit()">
    <option value="fr" <?= $lang==='fr'?'selected':'' ?>>Français</option>
    <option value="de" <?= $lang==='de'?'selected':'' ?>>Deutsch</option>
    <option value="it" <?= $lang==='it'?'selected':'' ?>>Italiano</option>
  </select>
</form>
<?php endif; ?>

<form method="get">
  <input type="hidden" name="server" value="<?= $server ?>">
  <?php if($server==='ch'): ?>
    <input type="hidden" name="lang" value="<?= $lang ?>">
  <?php endif; ?>
  <label for="country"><?= $t['select'] ?></label>
  <select name="country" id="country" onchange="this.form.submit()">
    <?php foreach($countries as $id=>$n): ?>
      <option value="<?= $id ?>" <?= $id==$selectedIdPays?'selected':'' ?>>
        <?= htmlspecialchars($n) ?>
      </option>
    <?php endforeach; ?>
  </select>
</form>

<label><input type="checkbox" id="colorblindToggle"> <?= $t['colorblind'] ?></label>

<!-- ✅ Bouton lecture vocale -->
<button id="readGraphBtn" class="accessibility-button" aria-label="Lecture graphique">🔊 Voice playback</button>

<div id="chartContainer" style="height:400px;width:100%;margin-top:20px;"></div>

<div class="button-container">
  <?php if($server==='us'): ?>
    <a href="graph.php?server=us"><button><?= $t['back_data'] ?></button></a>
    <a href="index_co.php?server=us"><button><?= $t['home'] ?></button></a>
  <?php elseif($server==='fr'): ?>
    <a href="index_co_fr.php?server=fr"><button><?= $t['home'] ?></button></a>
  <?php else: /* ch */ ?>
    <a href="index_co.php?server=ch&lang=<?= $lang ?>"><button><?= $t['home'] ?></button></a>
  <?php endif; ?>
</div>

<!-- ✅ Script de lecture vocale -->
<script>
document.getElementById("readGraphBtn").addEventListener("click", function() {
    const synth = window.speechSynthesis;
    synth.cancel();

    // Détermine la langue
    const lang = {
        fr: "fr-FR",
        en: "en-US",
        de: "de-DE",
        it: "it-IT"
    }["<?= $lang ?>"];

    const country = "<?= addslashes($selectedCountryName) ?>";
    const cumulData = <?= json_encode($dataPointsCumul, JSON_NUMERIC_CHECK) ?>;
    const predData  = <?= json_encode($dataPointsPredicted, JSON_NUMERIC_CHECK) ?>;

    let spokenText = {
        fr: `En haut de l'écran ce trouve un bouton permetant de choisir le pays à prédire avec l'IA. Lecture du cumul cas total pour le Covid et les prédictions de l'IA pour ${country}. `,
        en: `At the top of the screen, there is a button to select the country to predict with the AI. Reading cumulative cases for Covid and AI prediction for ${country}. `,
        de: `Oben auf dem Bildschirm befindet sich eine Schaltfläche, um das Land auszuwählen, das mit der KI vorhergesagt werden soll. Daten für ${country} werden vorgelesen. `,
        it: `In alto allo schermo c'è un pulsante per scegliere il paese da prevedere con l'IA. Lettura del totale dei casi di Covid e previsioni dell'IA per ${country}. `
    }["<?= $lang ?>"];

    if (cumulData.length > 0) {
        const lastReal = cumulData[cumulData.length - 1];
        const date = new Date(lastReal.x).toLocaleDateString("<?= $lang ?>", { year: 'numeric', month: 'long' });
        spokenText += {
            fr: `Cas cumulés au ${date} : ${lastReal.y}. `,
            en: `Cumulative cases as of ${date}: ${lastReal.y}. `,
            de: `Kumulative Fälle bis ${date}: ${lastReal.y}. `,
            it: `Casi cumulativi al ${date}: ${lastReal.y}. `
        }["<?= $lang ?>"];
    }

    if (predData.length > 1) {
        const lastPred = predData[predData.length - 1];
        const date = new Date(lastPred.x).toLocaleDateString("<?= $lang ?>", { year: 'numeric', month: 'long' });
        spokenText += {
            fr: `Prédiction IA pour ${date} : ${lastPred.y}. En bas de l'écran, il y a un bouton qui permet de retourner à l'accueil.`,
            en: `AI prediction for ${date}: ${lastPred.y}. At the bottom of the screen, there are three buttons that let you access other pages. Coronavirus global data, Back to home, and Coronavirus daily data.`,
            de: `KI-Vorhersage für ${date}: ${lastPred.y}. Unten auf dem Bildschirm befindet sich eine Schaltfläche, um zur Startseite zurückzukehren.`,
            it: `Previsione IA per ${date}: ${lastPred.y}. In basso allo schermo c'è un pulsante che permette di tornare alla home.`
        }["<?= $lang ?>"];
    }

    const utterance = new SpeechSynthesisUtterance(spokenText);
    utterance.lang = lang;
    utterance.rate = 1;
    synth.speak(utterance);
});
</script>

</body>
</html>
