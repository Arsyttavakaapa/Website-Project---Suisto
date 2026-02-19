<?php
header('Content-Type: application/json; charset=utf-8');

if (empty($_POST['tapahtumat'])) {
    echo json_encode(['error' => 'Ei tapahtumatietoa vastaanotettu']);
    exit;
}

$events = json_decode($_POST['tapahtumat']);
if (!$events || empty($events->id)) {
    echo json_encode(['error' => 'JSON ei ollut kelvollinen tai id puuttuu']);
    exit;
}

$id = $events->id;
$event_name = $events->event_name ?? '';
$event_date = $events->event_date ?? '';
$event_time = $events->event_time ?? '';
$description = $events->description ?? '';

$initials = parse_ini_file("../.ht_suisto.ini");
$yhteys = mysqli_connect($initials["server"], $initials["username"], $initials["password"], $initials["databasename"]);
if (!$yhteys) {
    echo json_encode(['error' => 'Tietokantayhteys epäonnistui']);
    exit;
}

// Päivitetään tapahtuma
$stmt = $yhteys->prepare("UPDATE events SET event_name=?, event_date=?, event_time=?, description=? WHERE id=?");
$stmt->bind_param("ssssi", $event_name, $event_date, $event_time, $description, $id);
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