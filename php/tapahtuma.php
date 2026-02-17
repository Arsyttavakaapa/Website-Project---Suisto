<?php
mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);
try{
    $yhteys=mysqli_connect("db", "root", "password", "suistodb");
}
catch(Exception $e){
    header("Location:../html/yhteysvirhe.html");
    exit;
}
$sql="select *, DAYNAME(event_date) as paiva from events where event_date >= CURDATE() and month(event_date)=? and year(event_date)=? order by event_date";

$tulos=mysqli_query($yhteys, $sql);
while ($rivi=mysqli_fetch_object($tulos)){
    $tapahtuma=new class{};
    $tapahtuma->id=$rivi->id;
    $tapahtuma->event_date=$rivi->event_date;
    $tapahtuma->event_name=$rivi->event_name;
    $tapahtuma->event_time=$rivi->event_time;
    $tapahtuma->description=$rivi->description;
    $tapahtuma->paiva=$rivi->paiva;
    $tapahtumat[]=$tapahtuma;
}
mysqli_close($yhteys);
print json_encode($tapahtumat);
?>