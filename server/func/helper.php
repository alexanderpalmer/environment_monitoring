<?php
function getFormattedDate($date) {
    $dateObject = new DateTime($date);
    return  $dateObject->format('d.m.Y H:i');
}

function showBoardState($lastUpdateDateTime) {
    if((int) round((strtotime($lastUpdateDateTime) - time()) / 60)<-5) {
      return '<span id="node-state-error">Offline</span>';
    }
    return '<span id="node-state-ok">Online</span>';
}
