<?php
mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);
try{
    $yhteys=mysqli_connect("db", "root", "password", "suistodb");
}
catch(Exception $e){
    header("Location:../html/yhteysvirhe.html");
    exit;
}
$tulos=mysqli_query($yhteys, "select distinct month(event_date) as month, year(event_date) as year from events where event_date >= CURDATE() order by year(event_date), month(event_date);");
while ($rivi=mysqli_fetch_object($tulos)){
    $paivays=new class{};
    $paivays->month=$rivi->month;
    $paivays->year=$rivi->year;
    $paivayslista[]=$paivays;
}
mysqli_close($yhteys);
print json_encode($paivayslista);
?>