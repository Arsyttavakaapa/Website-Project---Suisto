<?php
session_start();
if (isset($_POST["tunnus"]) && isset($_POST["salasana"])){
   $tunnus=$_POST["tunnus"];
   $salasana=$_POST["salasana"];
}
else{
    header("Location:./kirjaudu.html");
    exit;
}
$initials=parse_ini_file("../.ht_suisto.ini");
$yhteys=mysqli_connect($initials["server"],$initials["username"],$initials["password"],$initials["databasename"]);
$sql="select * from users where username=? and password=md5(?)";
$stmt=mysqli_prepare($yhteys, $sql);
mysqli_stmt_bind_param($stmt, "ss", $tunnus, $salasana);
mysqli_stmt_execute($stmt);
$tulos=mysqli_stmt_get_result($stmt);
if ($rivi=mysqli_fetch_object($tulos)){
    $_SESSION["user_ok"]="ok";
    header("Location:../ohjelmanmuokkaus.html");
    exit;
}
else{
        header("Location:../kirjaudu.html");
    exit;
}
?>