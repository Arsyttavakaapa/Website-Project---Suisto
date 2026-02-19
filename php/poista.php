<?php
session_start();
if (!isset($_SESSION["user_ok"])){
    echo false;
	exit;
}
mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);

try {
    $yhteys = mysqli_connect("db", "root", "password", "suistodb");
} catch(Exception $e) {
    header("Location:../html/yhteysvirhe.html");
    exit;
}

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Poistetaan ensin kaikki tiedot Tiketti-taulusta, jotka liittyvät tapahtumaan
    $stmt = $yhteys->prepare("delete from tiketti where event_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Sitten poistetaan itse tapahtuma
    $stmt = $yhteys->prepare("delete from events where id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if($stmt->affected_rows > 0){
        print "OK";
    } else {
        print "Ei poistettu";
    }
    $stmt->close();
}


$yhteys->close();
?>
