<?php

function connectDatabase() {
    $link = mysqli_connect('localhost', 'root', 'zbwzbw', 'environment-monitoring-simple') or 
    die('Verbindung zur Datenbank konnte nicht hergestellt werden!');
    return $link;
}

$link = connectDatabase();

function getBoardAndRoom($link) {
    $sql = 'SELECT sensorboard.board_id, sensorboard.seriennummer, raum.bezeichnung as raum
        FROM sensorboard 
        INNER JOIN raum
        ON sensorboard.raum_id = raum.raum_id';
    $result = mysqli_query($link, $sql);
    $boardInformations = array();
    while($row=mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $boardInformations[] = $row;
    }
    return $boardInformations;
}

function getDataForBoard($link, $boardID) {
    $sql='SELECT 
    messung.messung_id,
    messung.sensortyp_id,
    sensortyp.bezeichnung AS sensortyp,
    sensortyp.einheit AS einheit,
    sensortyp.symbol AS symbol,
    messung.zeitstempel,
    ROUND(messung.messwert,1) AS messwert
    FROM 
        messung
    INNER JOIN 
        sensortyp ON messung.sensortyp_id = sensortyp.sensortyp_id
    WHERE 
        messung.zeitstempel = (
            SELECT MAX(zeitstempel)
            FROM messung
            WHERE board_id = '.$boardID.'
    )
    AND messung.board_id = '.$boardID.'
    ORDER BY messung.sensortyp_id;';
    $result = mysqli_query($link, $sql);
    $sensorInformations = array();
    while($row=mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $sensorInformations[] = $row;
    }
    return $sensorInformations;
}

function getFormattedDate($date) {
    $dateObject = new DateTime($date);
    return  $dateObject->format('d.m.Y H:i');
}

function checkIfBoardOnline($lastUpdateDateTime) {
    if((int) round((strtotime($lastUpdateDateTime) - time()) / 60)<-5) {
      return 'node-state-error';
    }
    return 'node-state-ok';
}


?>
<!DOCTYPE html>
<html lang="de-CH">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <title>Environment Monitoring</title>
</head>

<body>
    <div id="wrapper">
        <header>
            <h1>Environment Monitoring</h1>
        </header>
        <main>
            <?php
            $boards = getBoardAndRoom($link);
            foreach($boards as $board) {
                $sensorInformations = getDataForBoard($link, $board['board_id']);
                if(!empty($sensorInformations)) {
            echo "
            <div id=\"node\">
                <h2>Node {$board['seriennummer']} - Raum {$board['raum']}</h2>
                <h3><span id=\"".checkIfBoardOnline(getFormattedDate($sensorInformations[0]['zeitstempel']))."\">Online</span> - letztes Update: ".getFormattedDate($sensorInformations[0]['zeitstempel'])." Uhr</h3>
                <table>
            ";
            foreach($sensorInformations as $value) {
                echo "
                <tr>
                    <td><img src=\"img/{$value['symbol']}\" alt=\"{$value['sensortyp']}\"></td>
                    <td>{$value['messwert']} {$value['einheit']}</td>
                </tr>
                ";
            }
            echo "
                </table>
                <p>
                    <button>Details</button>
                </p>
            </div>
            ";
                }
            }
            ?>
        </main>
        <footer>Das ist der Footer</footer>
    </div>
</body>

</html>
