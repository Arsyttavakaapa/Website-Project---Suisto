<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
if (!isset($_SESSION["user_ok"])){
    echo json_encode(['error'=>'Ei oikeuksia']);
    exit;
}

if (empty($_POST['tapahtumat'])) {
    echo json_encode(['error' => 'Ei tapahtumatietoa vastaanotettu']);
    exit;
}

$events = json_decode($_POST['tapahtumat'], true);
if (!$events || empty($events['id'])) {
    echo json_encode(['error' => 'JSON ei ollut kelvollinen tai id puuttuu']);
    exit;
}

$id = intval($events['id']);
$event_name = $events['event_name'] ?? '';
$event_date = $events['event_date'] ?? '';
$event_time = $events['event_time'] ?? '';
$description = $events['description'] ?? '';
$link = $events['link'] ?? '';

// Yhteys tietokantaan
$initials = parse_ini_file("../.ht_suisto.ini");
$yhteys = mysqli_connect($initials["server"], $initials["username"], $initials["password"], $initials["databasename"]);
if (!$yhteys) {
    echo json_encode(['error' => 'Tietokantayhteys epäonnistui']);
    exit;
}

// Päivitetään events-taulu
$stmt = $yhteys->prepare("UPDATE events SET event_name=?, event_date=?, event_time=?, description=? WHERE id=?");
$stmt->bind_param("ssssi", $event_name, $event_date, $event_time, $description, $id);
$stmt->execute();
$stmt->close();

// Päivitä, lisää tai poista Tiketti-linkki
if (!empty($link)) {
    // Tarkista onko linkki jo olemassa
    $result = mysqli_query($yhteys, "SELECT * FROM tiketti WHERE event_id=$id");
    if (mysqli_num_rows($result) > 0) {
        $stmt2 = $yhteys->prepare("UPDATE tiketti SET link=? WHERE event_id=?");
        $stmt2->bind_param("si", $link, $id);
        $stmt2->execute();
        $stmt2->close();
    } else {
        $stmt2 = $yhteys->prepare("INSERT INTO tiketti (event_id, link) VALUES (?, ?)");
        $stmt2->bind_param("is", $id, $link);
        $stmt2->execute();
        $stmt2->close();
    }
} else {
    // Linkki tyhjä → poista mahdollinen Tiketti-rivi
    mysqli_query($yhteys, "DELETE FROM tiketti WHERE event_id=$id");
}

// Palauta kaikki tapahtumat JSONina, yhdistäen Tiketti-linkki
$sql = "
SELECT e.id, e.event_name, e.event_date, TIME_FORMAT(e.event_time,'%H:%i') as event_time,
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