<?php
include "func/db_functions.php";
include "func/helper.php";
$link = connectDatabase();
// Beschaffung der Board-ID, welcher aus index.html via 
// dem Hyperlink um den Button transportiert wurde.
$boardID = $_GET['bid'];
//echo '<pre style="color: white">';
//var_dump(getAllSensorTypes($link));
//echo '</pre>';
?>
<!DOCTYPE html>
<html lang="de-CH">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environment Monitoring</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/details-styles.css">
</head>

<body>
    <div id="wrapper">
        <header>
            <h1>Environment Monitoring</h1>
        </header>
        <main>
	<?php
	$sensorTypes = getAllSensorTypes($link);
	foreach($sensorTypes as $sensor) {
	    echo '
	    <div id="value-table">
	    <h2>'.$sensor['beschreibung'].'</h2>
	    <div class="table-scroll-wrapper">
		<table>
		    <tr>
			<th>Zeit</th>
			<th>Messwert</th>
		    </tr>';
			    
	    $sensorData = getDataBySensorType($link, $boardID, $sensor['bezeichnung']);
	    foreach($sensorData as $data) {
		echo "<tr>
		<td>".getFormattedDate($data['zeitstempel'])."</td>
		<td>{$data['messwert']} {$data['einheit']}</td>
		</tr>";
	    }
	    echo '</table>
		</div>
	    </div>';
	}
	?>
        </main>
	 <footer>Das ist der Footer</footer>
    </div>
</body>
</html>
