<?php
header('Content-Type: application/json; charset=utf-8');

// Tarkistetaan, että POST-data on olemassa
if (empty($_POST['tapahtumat'])) {
    echo json_encode(['error' => 'Ei tapahtumatietoa vastaanotettu']);
    exit;
}

// JSON-datan purku
$tapahtuma = json_decode($_POST['tapahtumat']);
if (!$tapahtuma) {
    echo json_encode(['error' => 'JSON ei ollut kelvollinen']);
    exit;
}

// Hae tiedot turvallisesti
$event_name = $tapahtuma->event_name ?? '';
$event_date = $tapahtuma->event_date ?? '';
$event_time = $tapahtuma->event_time ?? '';
$description = $tapahtuma->description ?? '';

// Yhteys tietokantaan
$initials = parse_ini_file("../suisto website/.ht_suisto.ini");
$yhteys = mysqli_connect($initials["server"], $initials["username"], $initials["password"], $initials["databasename"]);
if (!$yhteys) {
    echo json_encode(['error' => 'Tietokantayhteys epäonnistui']);
    exit;
}

// Lisätään tapahtuma prepared statementilla
$stmt = $yhteys->prepare("INSERT INTO events (event_name, event_date, event_time, description) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $event_name, $event_date, $event_time, $description);
$stmt->execute();
$stmt->close();

// Palautetaan kaikki tapahtumat JSONina
$sql = "SELECT *, DAYNAME(event_date) as paiva, TIME_FORMAT(event_time, '%H:%i') as time, DATE_FORMAT(event_date, '%e.%c') as date FROM events WHERE event_date >= CURDATE() ORDER BY event_date";
$tulos = mysqli_query($yhteys, $sql);
$events = [];
while ($rivi = mysqli_fetch_object($tulos)) {
    $e = new stdClass();
    $e->id = $rivi->id;
    $e->event_name = $rivi->event_name;
    $e->event_date = $rivi->event_date;
    $e->event_time = $rivi->time;
    $e->description = $rivi->description;
    $e->paiva = $rivi->paiva;
    $events[] = $e;
}

mysqli_close($yhteys);
echo json_encode($events);
?>