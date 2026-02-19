<?php
error_reporting(E_ALL ^ E_WARNING);
mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);
$initials=parse_ini_file("../.ht_suisto.ini");
try{
    $yhteys=mysqli_connect($initials["server"],$initials["username"],$initials["password"],$initials["databasename"]);
}
catch(Exception $e){
    header("Location:../html/yhteysvirhe.html");
    exit;
}
$sql='select *, DAYNAME(event_date) as paiva, time_format(event_time, "%H:%i") as time, date_format(event_date, "%e.%c") as date from events left outer join tiketti on events.id=tiketti.event_id where event_date >= CURDATE() order by event_date;';
//valitaan data kahdesta datasetistä outer joinaamalla vasemmalle jossa päiväys on nykyisen päivän ajalta tai sen jälkeen, lisätään tulosteeseen viikonpäivän nimi, kellon aika formaatissa HH:MM ja päivämäärä formaatissa DD.MM, järjestäen tulos päivämäärän mukaan

$tulos=mysqli_query($yhteys, $sql);
while ($rivi=mysqli_fetch_object($tulos)){
//lisäämme tuloksen tiedot olioon, joka laitetaan listaan
    $tapahtuma=new class{};
    $tapahtuma->id=$rivi->id;
    $tapahtuma->date=$rivi->date;
    $tapahtuma->event_name=$rivi->event_name;
    $tapahtuma->time=$rivi->time;
    $tapahtuma->description=$rivi->description;
    $tapahtuma->paiva=$rivi->paiva;
    $tapahtuma->link=$rivi->link;
    $tapahtumat[]=$tapahtuma;
}
mysqli_close($yhteys);
print json_encode($tapahtumat);
?>