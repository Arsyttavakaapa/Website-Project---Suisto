<?php
mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);
try{
    $yhteys=mysqli_connect("db", "root", "password", "suistodb");
}
catch(Exception $e){
    print "Yhteysvirhe";
    exit;
}
$tulos=mysqli_query($yhteys, "select * from events");
while ($rivi=mysqli_fetch_object($tulos)){
    $event=new class{};
    $event->id=$rivi->id;
    $event->event_name=$rivi->event_name;
    $event->event_date=$rivi->event_date;
    $event->event_time=$rivi->event_time;
    $event->description=$rivi->description;
    $event->created=$rivi->created;
    $event->updated=$rivi->updated;

    $events[]=$event;
}
mysqli_close($yhteys);
print json_encode($events);

?>
