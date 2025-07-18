<?php
function connectDatabase() {
    $link = mysqli_connect('localhost', 'root', '', 'environment-monitoring-simple') or 
    die('Verbindung zur Datenbank konnte nicht hergestellt werden!');
    return $link;
}


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

function getAllSensorTypes($link) {
    $sql = "SELECT * FROM sensortyp";
    $result = mysqli_query($link, $sql);
    $sensorTypData = array();
    while($row=mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $sensorTypData[] = $row;
    }
    return $sensorTypData;
}

function getDataBySensorType($link, $boardID, $sensorDesc) {
    $sql='SELECT sensorboard.seriennummer, messung.zeitstempel, ROUND(messung.messwert,1) AS messwert,sensortyp.einheit 
    FROM messung INNER JOIN sensortyp
    ON messung.sensortyp_id=sensortyp.sensortyp_id 
    INNER JOIN sensorboard
    ON messung.board_id=sensorboard.board_id
    WHERE sensortyp.bezeichnung=\''.$sensorDesc.'\' AND 
    sensorboard.board_id=\''.$boardID.'\' ORDER BY messung.zeitstempel DESC';

    $result = mysqli_query($link, $sql);
    $sensorData = array();
    while($row=mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $sensorData[] = $row;
    }
    return $sensorData;
}

function getDataForDateFullRange($link, $boardID, $sensortypID) {
    $sql = "
        SELECT 
            DATE_FORMAT(zeitstempel, '%Y-%m-%dT%H:00:00') AS x,
            ROUND(AVG(messwert), 2) AS y
        FROM messung
        WHERE sensortyp_id = {$sensortypID} AND board_id = {$boardID}
        AND zeitstempel BETWEEN 
        (SELECT MIN(zeitstempel) FROM messung WHERE sensortyp_id = {$sensortypID} AND board_id = {$boardID})
        AND 
        (SELECT MAX(zeitstempel) FROM messung WHERE sensortyp_id = {$sensortypID} AND board_id = {$boardID})
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

    return $datapoints;
}