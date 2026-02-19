<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
if (!isset($_SESSION["user_ok"])){
    echo json_encode(['error' => 'Ei oikeuksia']);
    exit;
}

// Tarkistetaan, että POST-data on olemassa
if (empty($_POST['tapahtumat'])) {
    echo json_encode(['error' => 'Ei tapahtumatietoa vastaanotettu']);
    exit;
}

// JSON-datan purku
$tapahtuma = json_decode($_POST['tapahtumat'], true);
if (!$tapahtuma) {
    echo json_encode(['error' => 'JSON ei ollut kelvollinen']);
    exit;
}

$event_name = $tapahtuma['event_name'] ?? '';
$event_date = $tapahtuma['event_date'] ?? '';
$event_time = $tapahtuma['event_time'] ?? '';
$description = $tapahtuma['description'] ?? '';
$link = $tapahtuma['link'] ?? '';

// Yhteys tietokantaan
$initials = parse_ini_file("../.ht_suisto.ini");
$yhteys = mysqli_connect($initials["server"], $initials["username"], $initials["password"], $initials["databasename"]);
if (!$yhteys) {
    echo json_encode(['error' => 'Tietokantayhteys epäonnistui']);
    exit;
}

// Lisää tapahtuma events-tauluun
$stmt = $yhteys->prepare("INSERT INTO events (event_name, event_date, event_time, description) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $event_name, $event_date, $event_time, $description);
$stmt->execute();
$event_id = $stmt->insert_id;  // juuri lisätyn id
$stmt->close();

// Lisää Tiketti-linkki, jos annettu
if (!empty($link)) {
    $stmt2 = $yhteys->prepare("INSERT INTO tiketti (event_id, link) VALUES (?, ?)");
    $stmt2->bind_param("is", $event_id, $link);
    $stmt2->execute();
    $stmt2->close();
}

// Palautetaan kaikki tapahtumat yhdistäen Tiketti-linkki
$sql = "
SELECT 
    e.id, e.event_name, e.event_date, TIME_FORMAT(e.event_time,'%H:%i') as event_time,
    e.description, DAYNAME(e.event_date) as paiva, t.link
FROM events e
LEFT JOIN tiketti t ON e.id = t.event_id
WHERE e.event_date >= CURDATE()
ORDER BY e.event_date
";

$tulos = mysqli_query($yhteys, $sql);
$events = [];
while ($rivi = mysqli_fetch_object($tulos)) {
    $e = new stdClass();
    $e->id = $rivi->id;
    $e->event_name = $rivi->event_name;
    $e->event_date = $rivi->event_date;  // YYYY-MM-DD
    $e->event_time = $rivi->event_time;  // HH:mm
    $e->description = $rivi->description ?? '';
    $e->paiva = $rivi->paiva ?? '';
    $e->link = $rivi->link ?? '';
    $events[] = $e;
}

mysqli_close($yhteys);
echo json_encode($events, JSON_UNESCAPED_UNICODE);
?>
