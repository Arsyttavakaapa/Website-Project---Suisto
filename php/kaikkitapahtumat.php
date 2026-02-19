<?php
// Näytetään virheet kehitysvaiheessa
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Luetaan tietokantayhteys ini-tiedostosta
$initials = parse_ini_file("../suisto website/.ht_suisto.ini");

// Yhteyden muodostus
$yhteys = mysqli_connect(
    $initials["server"],
    $initials["username"],
    $initials["password"],
    $initials["databasename"]
);

if (!$yhteys) {
    http_response_code(500);
    echo json_encode(["error" => "Tietokantayhteys epäonnistui"]);
    exit;
}

// Kysely: haetaan kaikki tulevat tapahtumat
$sql = "
    SELECT events.id, events.event_name, events.event_date, events.event_time, events.description,
           DAYNAME(event_date) AS weekday,
           TIME_FORMAT(event_time, '%H:%i') AS time,
           DATE_FORMAT(event_date, '%Y-%m-%d') AS date,
           tiketti.link
    FROM events
    LEFT JOIN tiketti ON events.id = tiketti.event_id
    WHERE event_date >= CURDATE()
    ORDER BY event_date
";

$result = mysqli_query($yhteys, $sql);
$events = [];

while ($row = mysqli_fetch_object($result)) {
    $event = new stdClass();
    $event->id = $row->id;
    $event->event_name = $row->event_name;
    $event->event_date = $row->date;   // YYYY-MM-DD
    $event->event_time = $row->time;   // HH:MM
    $event->description = $row->description;
    $event->weekday = $row->weekday;   // Monday, Tuesday jne.
    $event->link = $row->link;
    $events[] = $event;
}

mysqli_close($yhteys);

// Palautetaan JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($events);
?>