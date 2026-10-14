<?php
session_start();

if(!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/db.php";

/*
DATE
*/
$date = isset($_GET['date'])
    ? $_GET['date']
    : date("Y-m-d");

/*
SEARCH
*/
$search = $_GET['search'] ?? '';

/*
GET UMBRELLAS
*/
$stmt = $pdo->query("
    SELECT *
    FROM beach_spots
    ORDER BY id ASC
");

$spots = $stmt->fetchAll();

/*
GET RESERVATIONS
*/
$reservationStmt = $pdo->prepare("
    SELECT *
    FROM reservations
    WHERE reservation_date = ?
    AND customer_name LIKE ?
");

$reservationStmt->execute([
    $date,
    "%$search%"
]);

$reservations =
    $reservationStmt->fetchAll();

/*
MAP RESERVATIONS
*/
$reservationMap = [];

foreach($reservations as $reservation) {

    $reservationMap[
        $reservation['beach_spot_id']
    ] = $reservation;
}

/*
STATISTICS
*/
$totalSeats = count($spots);

$totalReserved = count($reservations);

$totalFree =
    $totalSeats - $totalReserved;

$occupancy =
    $totalSeats > 0
    ? round(
        ($totalReserved / $totalSeats)
        * 100
    )
    : 0;
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Beach Dashboard</title>

    <link rel="stylesheet"
          href="../assets/style.css">

</head>

<body>

<div class="topbar">

    <div class="topbar-left">

        <h2>

            Beach Dashboard

        </h2>

        <form method="GET">

            <input
                type="date"
                name="date"
                value="<?php echo $date; ?>">

            <input
                type="text"
                name="search"
                placeholder="Search customer"
                value="<?php echo htmlspecialchars($search); ?>">

            <button type="submit">

                Search

            </button>

        </form>

    </div>

    <div
        style="
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        "
    >

        <a class="logout-btn"
           href="admin.php">

            Admin

        </a>

        <a class="logout-btn"
           href="../auth/logout.php">

            Logout

        </a>

    </div>

</div>

<!-- STATS -->

<div class="legend">

    <div class="legend-item">

        Total:
        <strong>
            <?php echo $totalSeats; ?>
        </strong>

    </div>

    <div class="legend-item">

        Reserved:
        <strong>
            <?php echo $totalReserved; ?>
        </strong>

    </div>

    <div class="legend-item">

        Free:
        <strong>
            <?php echo $totalFree; ?>
        </strong>

    </div>

    <div class="legend-item">

        Occupancy:
        <strong>
            <?php echo $occupancy; ?>%
        </strong>

    </div>

</div>

<!-- BEACH LAYOUT -->

<div class="layout">

<?php foreach($spots as $spot):

    $class = "free";

    $reservationData = null;

    if(isset($reservationMap[$spot['id']])) {

        $reservationData =
            $reservationMap[$spot['id']];

        $class =
            $reservationData['status'];
    }
?>

<div

    class="
        seat
        <?php echo $class; ?>
        <?php echo $spot['type']; ?>
    "

    data-id="<?php echo $spot['id']; ?>"

    data-name="<?php echo $spot['number_name']; ?>"

    data-reservation='<?php
        echo json_encode($reservationData);
    ?>'

    style="
        left: <?php echo $spot['pos_x']; ?>px;
        top: <?php echo $spot['pos_y']; ?>px;
    "

>

    <div class="seat-number">

        <?php echo $spot['number_name']; ?>

    </div>

</div>

<?php endforeach; ?>

</div>

<!-- MODAL -->

<div class="modal" id="reservationModal">

    <div class="modal-content">

        <h3 id="seatTitle"></h3>

        <form id="reservationForm">

            <input
                type="hidden"
                name="reservation_id"
                id="reservationId">

            <input
                type="hidden"
                name="beach_spot_id"
                id="beachSpotId">

            <input
                type="hidden"
                name="reservation_date"
                value="<?php echo $date; ?>">

            <!-- NAME -->

            <label>

                Customer Name

            </label>

            <input
                type="text"
                name="customer_name"
                id="customerName">

            <!-- PHONE -->

            <input
    type="text"
    name="phone"
    id="phone"
    placeholder="Phone">

<textarea
    name="comment"
    id="comment"
    placeholder="Comment"></textarea>

            <textarea
                name="comment"
                id="comment"></textarea>

            <!-- STATUS -->

            <label>

                Status

            </label>

            <select
                name="status"
                id="status">

                <option value="reserved">

                    Reserved

                </option>

                <option value="fake_reserved">

                    Fake Reserved

                </option>

            </select>

            <!-- BUTTONS -->

            <div
                style="
                    display:flex;
                    gap:10px;
                    flex-wrap:wrap;
                    margin-top:20px;
                "
            >

                <button
                    class="save-btn"
                    type="submit">

                    Save

                </button>

                <button
                    class="delete-btn"
                    type="button"
                    id="deleteBtn">

                    Delete

                </button>

                <button
                    class="close-btn"
                    type="button"
                    id="closeModal">

                    Close

                </button>

            </div>

        </form>

    </div>

</div>

<script src="../assets/app.js"></script>

</body>
</html>