<?php
error_reporting(E_ALL ^ E_WARNING);
mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);
$initials=parse_ini_file("../.ht_suisto.ini");
try{
    $yhteys=mysqli_connect($initials["server"],$initials["username"],$initials["password"],$initials["databasename"],);
}
catch(Exception $e){
    header("Location:../html/yhteysvirhe.html");
    exit;
}
$month=isset($_GET["month"]) ? $_GET["month"] : "";
//saamme kuukauden jonkla lisäämme muuttujaan
$year=isset($_GET["year"]) ? $_GET["year"] : "";
//saamme vuoden jonka lisäämme muuttujaan
$sql='select *, DAYNAME(event_date) as paiva, time_format(event_time, "%H:%i") as time, date_format(event_date, "%e.%c") as date from events left outer join tiketti on events.id=tiketti.event_id where event_date >= CURDATE() and month(event_date)=? and year(event_date)=? order by event_date';
//valitaan data kahdesta tabesta jotka outer joinataan vasemmalle jossa kuukausi on ? ja vuosi on ?, sekä päiväys on nykyisen päivän ajalta tai sen jälkeen. Lisätään tulosteeseen viikonpäivän nimi, kellon aika formaatissa HH:MM ja päivämäärä formaatissa DD.MM, järjestäen tulos päivämäärän mukaan
$stmt=mysqli_prepare($yhteys, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $month, $year);
//lisäämme ? paikalle arvot saaduista muuttujista
mysqli_stmt_execute($stmt);
//suoritamme komennon
mysqli_stmt_bind_result($stmt, $id, $event_name, $event_date, $event_time, $description, $created, $updated, $tiketti_id, $event_id, $link, $link_created, $link_updated, $paiva, $time, $date);
//pyydämme komennosta saadun tuloksen TÄRKEÄ: Pitää pyytää *kaikki* tuloksessa saadut arvot
$tulos=mysqli_stmt_get_result($stmt);
//Lisäämme saadun tuloksen talteen olioon
while ($rivi=mysqli_fetch_object($tulos)){
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