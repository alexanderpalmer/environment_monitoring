<?php
include "func/db_functions.php";
include "func/helper.php";
$link = connectDatabase();
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
    <div id="reload-progress"></div>
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
                        <h3>".showBoardState(getFormattedDate($sensorInformations[0]['zeitstempel'])).
                            " - letztes Update: ".getFormattedDate($sensorInformations[0]['zeitstempel']).
                        " Uhr</h3>
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
                            <a href=\"details.php?bid={$board['board_id']}\"><button type=\"button\">Details</button></a>
                        </p>
                    </div>
                    ";
                }
            }
            ?>
        </main>
        <footer>Das ist der Footer</footer>
    </div>

    <script>
    const durationInMinutes = 1;
    const totalSeconds = durationInMinutes * 60;
    let secondsLeft = totalSeconds;

    const progressBar = document.getElementById('reload-progress');

    const interval = setInterval(() => {
        secondsLeft--;
        const percent = (secondsLeft / totalSeconds) * 100;
        progressBar.style.width = percent + '%';

        if (secondsLeft <= 0) {
            clearInterval(interval);
            location.reload();
        }
    }, 1000);
</script>
</body>

</html>
