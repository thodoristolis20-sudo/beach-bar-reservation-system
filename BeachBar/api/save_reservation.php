<?php

session_start();

require_once "../config/db.php";

/*
CHECK LOGIN
*/

if(!isset($_SESSION["user_id"])) {

    die("Unauthorized");
}

/*
GET POST DATA
*/

$reservationId =
    $_POST["reservation_id"] ?? "";

$beachSpotId =
    $_POST["beach_spot_id"] ?? "";

$reservationDate =
    $_POST["reservation_date"] ?? "";

$customerName =
    trim($_POST["customer_name"] ?? "");

$phone =
    trim($_POST["phone"] ?? "");

$comment =
    trim($_POST["comment"] ?? "");

$status =
    $_POST["status"] ?? "reserved";

$userId =
    $_SESSION["user_id"];

/*
VALIDATION
*/

if(
    empty($beachSpotId) ||
    empty($reservationDate)
) {

    die("Missing required fields");
}

/*
VALID STATUSES
*/

$allowedStatuses = [
    "reserved",
    "fake_reserved"
];

if(!in_array($status, $allowedStatuses)) {

    die("Invalid reservation status");
}

/*
NAME REQUIRED ONLY FOR REAL RESERVATIONS
*/

if(
    $status === "reserved" &&
    empty($customerName)
) {

    die("Customer name is required");
}

/*
FOR FAKE RESERVATIONS
AUTO SET NAME
*/

if(
    $status === "fake_reserved" &&
    empty($customerName)
) {

    $customerName = "Fake Reservation";
}

/*
UPDATE EXISTING RESERVATION
*/

if($reservationId != "") {

    $stmt = $pdo->prepare("
        UPDATE reservations
        SET
            customer_name = ?,
            phone = ?,
            comment = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->execute([

        $customerName,
        $phone,
        $comment,
        $status,
        $reservationId

    ]);

    die("Reservation updated");
}

/*
CHECK IF SEAT ALREADY RESERVED
*/

$checkStmt = $pdo->prepare("
    SELECT id
    FROM reservations
    WHERE beach_spot_id = ?
    AND reservation_date = ?
");

$checkStmt->execute([

    $beachSpotId,
    $reservationDate

]);

$existingReservation =
    $checkStmt->fetch();

/*
BLOCK DOUBLE RESERVATION
*/

if($existingReservation) {

    die("Seat already reserved for this date");
}

/*
INSERT NEW RESERVATION
*/

$stmt = $pdo->prepare("
    INSERT INTO reservations
    (
        beach_spot_id,
        reservation_date,
        customer_name,
        phone,
        comment,
        status,
        created_by
    )
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([

    $beachSpotId,
    $reservationDate,
    $customerName,
    $phone,
    $comment,
    $status,
    $userId

]);

echo "Reservation saved";

?>