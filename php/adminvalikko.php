<?php
session_start();
if (!isset($_SESSION["user_ok"])){
    echo false;
	exit;
}
error_reporting(E_ALL ^ E_WARNING);
mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);
$initials=parse_ini_file("../suisto website/.ht_suisto.ini");
try{
    $yhteys=mysqli_connect($initials["server"],$initials["username"],$initials["password"],$initials["databasename"],);
}
catch(Exception $e){
    header("Location:../html/yhteysvirhe.html");
    exit;
}
$tulos=mysqli_query($yhteys, "select distinct month(event_date) as month, year(event_date) as year from events order by year(event_date), month(event_date);");
//valitaan uniikki data jossa on eri kuukaudet ja vuosi, jossa päivämäärä on joko nyt tai tulevaisuudessa, järjestäen data päivämäärän mukaan
while ($rivi=mysqli_fetch_object($tulos)){
//lisäämme tulokset talteen
    $paivays=new class{};
    $paivays->month=$rivi->month;
    $paivays->year=$rivi->year;
    $paivayslista[]=$paivays;
}
mysqli_close($yhteys);
print json_encode($paivayslista);
?>