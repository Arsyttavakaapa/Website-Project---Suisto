<?php

$json = isset($_POST["events"]) ? $_POST["events"] : "";

if (empty($json)) {
    print "Täytä kaikki kentät";
    exit;
}

$events = json_decode($json);

if (!$events || 
    !isset($events->event_name, 
            $events->event_date, 
            $events->event_time, 
            $events->description)) {
    print "Virheellinen JSON tai puuttuvia kenttiä";
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $yhteys = mysqli_connect("db", "root", "password", "suistodb");

    $sql = "INSERT INTO events 
            (event_name, event_date, event_time, description) 
            VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($yhteys, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        'ssss',
        $events->event_name,
        $events->event_date,
        $events->event_time,
        $events->description
    );

    mysqli_stmt_execute($stmt);

    $tulos = mysqli_query($yhteys, 
    "SELECT * FROM events ORDER BY event_date, event_time");


    $eventst = [];

    while ($rivi = mysqli_fetch_object($tulos)) {
        $event = new stdClass();
        $event->id = $rivi->id; // ✅ ADD THIS
        $event->event_name = $rivi->event_name;
        $event->event_date = $rivi->event_date;
        $event->event_time = $rivi->event_time;
        $event->description = $rivi->description;
        $eventst[] = $event;
    }

    mysqli_close($yhteys);

    print json_encode($eventst);

} catch (Exception $e) {
    print "Tietokantavirhe";
}
?>