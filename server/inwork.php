<?php

require("phpMQTT.php");
$link = mysqli_connect('localhost', 'root', 'zbwzbw', 'environment-monitoring-simple') or 
die('Verbindung zur Datenbank konnte nicht hergestellt werden!');

$server = "192.168.1.130";         // bitte anpassen
$port = 1883;                      // bitte anpassen
$username = "";                    // benutzername
$password = "";                    // passwort
$client_id = "123"; 			   // eindeutige Client-ID ist zwingend

$mqtt = new phpMQTT($server, $port, $client_id);

if (!$mqtt->connect(true, NULL, $username, $password)) {
	exit(1);
}


$topics['ZbW/Monitor'] = array("qos" => 0, "function" => "saveData");
$mqtt->subscribe($topics, 0);

while ($mqtt->proc()) { }


$mqtt->close();
function procmsg($topic, $msg)
{
	echo "Msg Recieved: " . date("r") . "\n";
	echo "Topic: {$topic}\n\n";
	echo "\t$msg\n\n";
}

function saveData($topic, $msg)
{
	global $link;
	// Beschaffen des aktuellen Datums im amerikansichen Format
	$date = date('Y-m-d');
	// Beschaffen der aktuellen Uhrzeit auf dem Server
	$time = date('H:i:s');
	$value = intval($msg);
	
	// Extrahiert die Schlüssel und die Werte aus $msg
	/* in der Form von
	array(5) {
	  ["board"]=> string(4) "1001"
	  ["temp"]=> string(2) "28"
	  ["hum"]=> string(2) "53"
	  ["pres"]=> string(3) "945" 
	  ["lum"]=> string(3) "158"
	}	
	 */
	$data = explode(';',$msg);
	$sensorValuesByName = [];
	foreach($data as $pair) {
		list($key, $value) = explode(':',$pair);
		$sensorValuesByName[trim($key)] = trim($value); 
	}
	
	// Seriennummer des Boards auslesen und dann Boardinformation aus
	// Array entfernen
	$boardSerialNumber = $sensorValuesByName['board'];
	unset($sensorValuesByName['board']);
	
	// Beschaffen des Primärschlüssels des Boards
	$sql = "SELECT board_id FROM sensorboard WHERE seriennummer='$boardSerialNumber'";
	$result = mysqli_query($link, $sql);
	$row = mysqli_fetch_array($result);
	$boardID = $row['board_id'];

	
	// Erstellt einen kommaseparierten String, um die Sensorbezeichnungen
	// zu extrahieren
	$sensorDescription = '';
	foreach($sensorValuesByName as $key=>$value) {
		$sensorDescription .= "'".$key."', ";
	}
	
	// Entfernt das letzte Komma
	$sensorDescription = rtrim(trim($sensorDescription), ',');
	// In $sensorValuesByID stehen nun die Bezeichnungen der Sensoren sowie
	// deren Primärschlüssel drin
	/*
	 array(4) {
       ["hum"]=> string(1) "2"
       ["lum"]=> string(1) "4"
       ["pres"]=> string(1) "3"
       ["temp"]=> string(1) "1"
     }
	 */
	$sensorsIDsByName = [];
	$sql = "SELECT sensortyp_id, bezeichnung FROM sensortyp WHERE bezeichnung IN ($sensorDescription);";
	$result = mysqli_query($link, $sql);
	while($row = mysqli_fetch_array($result)){
		$sensorsIDsByName[trim($row['bezeichnung'])] = trim($row['sensortyp_id']);
	}

	// Verarbeiten des Arrays mit den Sensordaten. Diese werden SQL-Statements umgewandelt
	foreach($sensorValuesByName as $key=>$sensor) {
		$timestamp = $date.' '.$time;
		$sql = "INSERT INTO messung (messung_id, board_id, sensortyp_id, zeitstempel, messwert) 
		VALUES (NULL, $boardID, $sensorsIDsByName[$key], '$timestamp', $sensorValuesByName[$key]);";
		$status = mysqli_query($link, $sql);
		echo $status . "\n";
	}
}
