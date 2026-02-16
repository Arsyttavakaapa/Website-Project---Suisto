<?php

$json=isset($_POST["events"]) ? $_POST["events"] : "";
if (empty($json)){
    print "Täytä kaikki kentät";
    exit;
}
$events=json_decode($json, false);

// if (!($events=tarkistaJson($json))){
//     print "Täytä kaikki kentät";
//     exit;
// }

mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try{
    $yhteys=mysqli_connect("db", "root", "password", "suistodb");
}
catch(Exception $e){
    print "Yhteysvirhe";
    exit;
}

//Tehdään sql-lause, jossa kysymysmerkeillä osoitetaan paikat
//joihin laitetaan muuttujien arvoja
$sql="insert into events (event_name, event_date, event_time, ".
"description, created, updated) values(?, ?, ?, ?, ?, ?)";

//Valmistellaan sql-lause
$stmt=mysqli_prepare($yhteys, $sql);
//Sijoitetaan muuttujat oikeisiin paikkoihin
mysqli_stmt_bind_param($stmt, 'ssssss', $events->event_name, 
$events->event_date, $events->event_time, $events->description, 
$events->created, $events->updated);
//Suoritetaan sql-lause
mysqli_stmt_execute($stmt);

$tulos=mysqli_query($yhteys, "select * from events");

while ($rivi=mysqli_fetch_object($tulos)){
    $events=new class{};
    $events->event_name=$rivi->event_name;
    $events->event_date=$rivi->event_date;
    $events->event_time=$rivi->event_time;
    $events->description=$rivi->description;
    $events->created=$rivi->created;
    $events->updated=$rivi->updated;
    $eventst[]=$events;
}


//Suljetaan tietokantayhteys
mysqli_close($yhteys);
print json_encode($eventst);
?>
<?php
function tarkistaJson($json){
    if (empty($json)){
        return false;
    }
    $events=json_decode($json, false);
    if (empty($events->event_name) || empty($events->event_date) || 
    empty($events->event_time) || empty($events->description) || 
    empty($events->created) || empty($events->updated)){
        return false;
    }
    return $events;
}
?>