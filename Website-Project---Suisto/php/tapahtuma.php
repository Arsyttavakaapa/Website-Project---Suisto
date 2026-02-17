<?php

mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);
try{
    $yhteys=mysqli_connect("db", "root", "password", "suistodb");
}
catch(Exception $e){
    header("Location:../html/yhteysvirhe.html");
    exit;
}
$month=isset($_GET["month"]) ? $_GET["month"] : "";
$year=isset($_GET["year"]) ? $_GET["year"] : "";
$sql='select *, DAYNAME(event_date) as paiva, time_format(event_time, "%H:%i") as time, date_format(event_date, "%e.%c") as date from events where event_date >= CURDATE() and month(event_date)=? and year(event_date)=? order by event_date';

$stmt=mysqli_prepare($yhteys, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $month, $year);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $id, $event_name, $event_date, $event_time, $description, $created, $updated, $paiva, $time, $date);
$tulos=mysqli_stmt_get_result($stmt);
while ($rivi=mysqli_fetch_object($tulos)){
    $tapahtuma=new class{};
    $tapahtuma->id=$rivi->id;
    $tapahtuma->date=$rivi->date;
    $tapahtuma->event_name=$rivi->event_name;
    $tapahtuma->time=$rivi->time;
    $tapahtuma->description=$rivi->description;
    $tapahtuma->paiva=$rivi->paiva;
    $tapahtumat[]=$tapahtuma;
}
mysqli_close($yhteys);
print json_encode($tapahtumat);
?>