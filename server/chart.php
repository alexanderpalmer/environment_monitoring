<?php
// Beschaffung der Board-ID, welcher aus index.html via 
// dem Hyperlink um den Button transportiert wurde.
$boardID = $_GET['bid'];
// Beschaffung der Sensortyp-ID, welcher aus index.html via 
// dem Hyperlink um den Button transportiert wurde.
$sensortypID = $_GET['sid'];
// Beschaffung der Sensortyp-Beschreibung, welcher aus index.html via 
// dem Hyperlink um den Button transportiert wurde.
$sensortypDesc = $_GET['des'];


// Verbindung zur MariaDB aufbauen
$link = mysqli_connect('localhost', 'root', '', 'environment-monitoring-simple') 
    or die('Verbindung zur Datenbank konnte nicht hergestellt werden!');

$sql = "
    SELECT 
        DATE_FORMAT(zeitstempel, '%Y-%m-%dT%H:00:00') AS x,
        ROUND(AVG(messwert), 2) AS y
    FROM messung
    WHERE sensortyp_id = {$sensortypID} AND board_id = {$boardID}
      AND zeitstempel BETWEEN '2025-07-01 00:00:00' AND '2025-07-03 23:59:59'
    GROUP BY x
    ORDER BY x ASC;
";
$result = mysqli_query($link, $sql);

// Daten vorbereiten für Chart.js (x = Zeit, y = Temperaturwert)
$datapoints = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $datapoints[] = [
            "x" => $row['x'],
            "y" => (float)$row['y']
        ];
    }
}

// Verbindung schließen
mysqli_close($link);

// Daten als JSON für JavaScript
$datapoints_json = json_encode($datapoints);
?>
<!DOCTYPE html>
<html lang="de-CH">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environment Monitoring</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/chart-styles.css">
    <!-- Chart.js (UMD-Version) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- Luxon & Adapter für Zeitachsen -->
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.0/dist/chartjs-adapter-luxon.umd.min.js"></script>
</head>
<body>
<div id="wrapper">
    <header>
        <h1>Environment Monitoring</h1>
    </header>
    <main>
    <canvas id="chart"></canvas>
    </main>
</div>
<script>
        const datapoints = <?php echo $datapoints_json; ?>;

        const ctx = document.getElementById('chart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: '<?php echo $sensortypDesc; ?>',
                    data: datapoints,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(49, 9, 18, 0.2)',
                    borderWidth: 2,
                    pointRadius: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            tooltipFormat: 'dd.MM.yyyy HH:mm',
                            displayFormats: {
                                hour: 'dd.MM. HH:mm', 
                                minute: 'dd.MM. HH:mm'
                            }
                        },
                        ticks: {
                            rotation: 90,
                            autoSkip: true,
                            maxTicksLimit: 20,
                            color: 'white'
                        },
                        title: {
                            display: true,
                            text: 'Zeit',
                            color: 'white'
                        }
                    },
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: '<?php echo $sensortypDesc; ?>'
                        },
                        ticks: {
                            color: 'white'
                        },
                        title: {
                            color: 'white'
                        }

                    },
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: 'white'
                        }
                    },
                    tooltip: {
                        mode: 'nearest',
                        intersect: false,
                    }
                }
            }
        });
    </script>
     <footer>
	 <ul>
	    <li><a href="index.php"><img src="img/home_medium.png"> <span class="nav-text">Zurück zur Übersicht</span></a></li>
	    <li><a href="#" id="reload"><img src="img/reload_medium.png"> <span class="nav-text">Daten aktualisieren</span></a></li>
	 </ul>
	 </footer>
</body>
</html>