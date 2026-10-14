<?php
session_start();

if(!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/db.php";

/*
ADMIN CODE
*/
$adminCode = "3511";

/*
ADMIN SESSION
*/
if(!isset($_SESSION["admin_verified"])) {

    $_SESSION["admin_verified"] = false;
}

/*
LOCK ADMIN
*/
if(isset($_GET["logout_admin"])) {

    $_SESSION["admin_verified"] = false;

    header("Location: admin.php");
    exit;
}

/*
CHECK ADMIN CODE
*/
$error = "";

if(isset($_POST["admin_code"])) {

    $enteredCode =
        trim($_POST["admin_code"]);

    if($enteredCode === $adminCode) {

        $_SESSION["admin_verified"] = true;

        header("Location: admin.php");
        exit;

    } else {

        $error = "Wrong admin code";
    }
}

/*
BLOCK ACCESS
*/
if($_SESSION["admin_verified"] !== true):
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>Admin Access</title>

<link rel="stylesheet"
      href="../assets/style.css">

</head>

<body>

<div class="auth-container">

    <h2>Admin Access</h2>

    <p class="auth-subtitle">

        Enter admin security code

    </p>

    <?php if($error != ""): ?>

        <div class="auth-message auth-error">

            <?php echo $error; ?>

        </div>

    <?php endif; ?>

    <form method="POST">

        <input
            type="password"
            name="admin_code"
            placeholder="Admin Code"
            required>

        <button type="submit">

            Enter Admin Panel

        </button>

    </form>

</div>

</body>
</html>

<?php
exit;
endif;

/*
GRID SETTINGS
*/
$gridSize = 80;

/*
ADD UMBRELLA
*/
if(isset($_POST["add_seat"])) {

    $numberName =
        trim($_POST["number_name"]);

    $posX =
        intval($_POST["pos_x"]);

    $posY =
        intval($_POST["pos_y"]);

    $type =
        $_POST["type"];

    /*
    SNAP TO GRID
    */

    $posX =
        round($posX / $gridSize)
        * $gridSize;

    $posY =
        round($posY / $gridSize)
        * $gridSize;

    $stmt = $pdo->prepare("
        INSERT INTO beach_spots
        (
            number_name,
            pos_x,
            pos_y,
            type
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([

        $numberName,
        $posX,
        $posY,
        $type

    ]);

    header("Location: admin.php");
    exit;
}

/*
DELETE UMBRELLA
*/
if(isset($_GET["delete"])) {

    $id =
        intval($_GET["delete"]);

    $deleteReservations =
        $pdo->prepare("
            DELETE FROM reservations
            WHERE beach_spot_id = ?
        ");

    $deleteReservations->execute([$id]);

    $stmt = $pdo->prepare("
        DELETE FROM beach_spots
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header("Location: admin.php");
    exit;
}

/*
GET UMBRELLAS
*/
$spots = $pdo->query("
    SELECT *
    FROM beach_spots
    ORDER BY id ASC
")->fetchAll();

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>Admin Panel</title>

<link rel="stylesheet"
      href="../assets/style.css">

<style>

.editor-layout {

    position: relative;

    width: 100%;

    min-height: 1400px;

    overflow: auto;

    background-image:

        linear-gradient(
            rgba(255,255,255,0.08) 1px,
            transparent 1px
        ),

        linear-gradient(
            90deg,
            rgba(255,255,255,0.08) 1px,
            transparent 1px
        );

    background-size:
        80px 80px;
}

.seat-preview {

    touch-action: none;
}

.grid-info {

    margin-top: 15px;

    font-size: 14px;

    opacity: 0.7;
}

</style>

</head>

<body>

<div class="topbar">

    <div class="topbar-left">

        <h1>

            Beach Grid Editor

        </h1>

    </div>

    <div
        style="
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        "
    >

        <a class="logout-btn"
           href="dashboard.php">

            Dashboard

        </a>

        <a class="logout-btn"
           href="?logout_admin=1">

            Lock Admin

        </a>

    </div>

</div>

<!-- ADD UMBRELLA -->

<div class="admin-card">

    <h2
        style="
            margin-bottom:20px;
        "
    >

        Add Umbrella

    </h2>

    <form
        method="POST"
        style="
            display:flex;
            gap:12px;
            flex-wrap:wrap;
        "
    >

        <input
            type="text"
            name="number_name"
            placeholder="Umbrella Number"
            required>

        <input
            type="number"
            name="pos_x"
            placeholder="X Position"
            required>

        <input
            type="number"
            name="pos_y"
            placeholder="Y Position"
            required>

        <select name="type">

            <option value="normal">

                Normal

            </option>

            <option value="vip">

                VIP

            </option>

        </select>

        <button
            type="submit"
            name="add_seat"
            class="save-btn">

            Add Umbrella

        </button>

    </form>

    <div class="grid-info">

        Grid size: 80px × 80px

    </div>

</div>

<!-- GRID EDITOR -->

<div
    class="layout editor-layout"
    id="editorLayout"
>

<?php foreach($spots as $spot): ?>

<div

    class="
        seat
        seat-preview
        <?php echo $spot['type']; ?>
        free
    "

    data-id="<?php echo $spot['id']; ?>"

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

<div class="drag-mode">

    Grid snapping enabled

</div>

<script>

const GRID_SIZE = 80;

const seats =
    document.querySelectorAll('.seat-preview');

let activeSeat = null;

let startX = 0;
let startY = 0;

let seatStartLeft = 0;
let seatStartTop = 0;

/*
START DRAG
*/

function startDrag(clientX, clientY, seat) {

    activeSeat = seat;

    startX = clientX;
    startY = clientY;

    seatStartLeft =
        parseInt(seat.style.left);

    seatStartTop =
        parseInt(seat.style.top);

    seat.style.zIndex = 999;
}

/*
MOVE
*/

function moveDrag(clientX, clientY) {

    if(!activeSeat) return;

    const dx =
        clientX - startX;

    const dy =
        clientY - startY;

    let newX =
        seatStartLeft + dx;

    let newY =
        seatStartTop + dy;

    /*
    SNAP TO GRID
    */

    newX =
        Math.round(newX / GRID_SIZE)
        * GRID_SIZE;

    newY =
        Math.round(newY / GRID_SIZE)
        * GRID_SIZE;

    activeSeat.style.left =
        newX + 'px';

    activeSeat.style.top =
        newY + 'px';
}

/*
END
*/

async function endDrag() {

    if(!activeSeat) return;

    activeSeat.style.zIndex = 1;

    const formData = new FormData();

    formData.append(
        'id',
        activeSeat.dataset.id
    );

    formData.append(
        'x',
        parseInt(activeSeat.style.left)
    );

    formData.append(
        'y',
        parseInt(activeSeat.style.top)
    );

    await fetch(
        '../api/update_position.php',
        {
            method: 'POST',
            body: formData
        }
    );

    activeSeat = null;
}

/*
MOUSE
*/

seats.forEach(seat => {

    seat.addEventListener(
        'mousedown',
        (e) => {

        e.preventDefault();

        startDrag(
            e.clientX,
            e.clientY,
            seat
        );

    });

});

/*
MOUSE MOVE
*/

document.addEventListener(
    'mousemove',
    (e) => {

    moveDrag(
        e.clientX,
        e.clientY
    );

});

/*
MOUSE UP
*/

document.addEventListener(
    'mouseup',
    endDrag
);

/*
TOUCH START
*/

seats.forEach(seat => {

    seat.addEventListener(
        'touchstart',
        (e) => {

        const touch =
            e.touches[0];

        startDrag(
            touch.clientX,
            touch.clientY,
            seat
        );

    },
    { passive: false });

});

/*
TOUCH MOVE
*/

document.addEventListener(
    'touchmove',
    (e) => {

    if(!activeSeat) return;

    const touch =
        e.touches[0];

    moveDrag(
        touch.clientX,
        touch.clientY
    );

},
{ passive: false });

/*
TOUCH END
*/

document.addEventListener(
    'touchend',
    endDrag
);

</script>

</body>
</html>