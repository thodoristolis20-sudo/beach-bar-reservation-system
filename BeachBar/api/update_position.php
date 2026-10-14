<?php

session_start();

require_once '../config/db.php';

/*
CHECK LOGIN
*/

if(!isset($_SESSION['user_id'])) {

    die('Unauthorized');
}

/*
GET DATA
*/

$id =
    intval($_POST['id']);

$x =
    intval($_POST['x']);

$y =
    intval($_POST['y']);

/*
UPDATE POSITION
*/

$stmt = $pdo->prepare(
    "
    UPDATE beach_spots
    SET
        pos_x = ?,
        pos_y = ?
    WHERE id = ?
    "
);

$stmt->execute([

    $x,
    $y,
    $id

]);

echo 'Updated';

?>