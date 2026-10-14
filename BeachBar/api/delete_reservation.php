<?php
session_start();

require_once "../config/db.php";

if(!isset($_SESSION["user_id"])) {
    die("Unauthorized");
}

$reservationId = $_POST["reservation_id"];

$stmt = $pdo->prepare("
    DELETE FROM reservations
    WHERE id = ?
");

$stmt->execute([$reservationId]);

echo "Reservation deleted";
?>