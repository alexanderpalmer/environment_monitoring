<?php
include "func/db_functions.php";
$link = connectDatabase();
// Beschaffung der Board-ID, welcher aus index.html via 
// dem Hyperlink um den Button transportiert wurde.
$boardID = $_GET['bid'];
// Beschaffung der Sensortyp-ID, welcher aus index.html via 
// dem Hyperlink um den Button transportiert wurde.
$sensortypID = $_GET['sid'];
// Beschaffung der Sensortyp-Beschreibung, welcher aus index.html via 
// dem Hyperlink um den Button transportiert wurde.
$sensortypDesc = $_GET['des'];

// Beschaft
$datapoints = getDataForDateFullRange($link, $boardID, $sensortypID);

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
    <form action="">
        <label for="date-from">Von: </label>
        <select id="date-from">
            <option>01.07.2025</option>
            <option>02.07.2025</option>
            <option>03.07.2025</option>
            <option>04.07.2025</option>
        </select>

        <label for="date-to">Bis: </label>
        <select id="date-to">
            <option>01.07.2025</option>
            <option>02.07.2025</option>
            <option>03.07.2025</option>
            <option>04.07.2025</option>
        </select>
    </form>
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
	    <li><a href="details.php?bid=<?php echo $boardID; ?>"><img src="img/home_medium.png"> <span class="nav-text">Zurück zur Übersicht</span></a></li>
	    <li><a href="#" id="reload"><img src="img/reload_medium.png"> <span class="nav-text">Daten aktualisieren</span></a></li>
	 </ul>
	 </footer>
</body>
</html>