<?php

$json = $_POST["events"] ?? "";

if (empty($json)) {
    exit("Virheellinen pyyntö");
}

$event = json_decode($json);

if (!$event || 
    !isset($event->id,
            $event->event_name,
            $event->event_date,
            $event->event_time,
            $event->description)) {
    exit("Puuttuvia tietoja");
}

$yhteys = mysqli_connect("db", "root", "password", "suistodb");

if (!$yhteys) {
    exit("Yhteysvirhe");
}

$sql = "UPDATE events 
        SET event_name=?, event_date=?, event_time=?, description=? 
        WHERE id=?";

$stmt = mysqli_prepare($yhteys, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssssi",
    $event->event_name,
    $event->event_date,
    $event->event_time,
    $event->description,
    $event->id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* Return updated list */
$tulos = mysqli_query($yhteys, 
    "SELECT * FROM events ORDER BY event_date, event_time");

$events = [];

while ($rivi = mysqli_fetch_object($tulos)) {
    $events[] = $rivi;
}

mysqli_close($yhteys);

echo json_encode($events);
?>
