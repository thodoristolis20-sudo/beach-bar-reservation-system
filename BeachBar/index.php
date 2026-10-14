<?php
session_start();

if(isset($_SESSION['user_id'])) {

    header("Location: pages/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Beach Bar Reservation System</title>

    <link rel="stylesheet"
          href="assets/style.css">

</head>

<body>

<div class="auth-container">

    <h1>Beach Bar</h1>

    <p class="auth-subtitle">
        Reservation Management System
    </p>

    <div
        style="
            display:flex;
            flex-direction:column;
            gap:15px;
        "
    >

        <a
            href="auth/login.php"
            class="logout-btn"
            style="
                justify-content:center;
                text-align:center;
            "
        >

            Login

        </a>

        <a
            href="auth/register.php"
            class="save-btn"
            style="
                text-decoration:none;
                text-align:center;
                display:flex;
                justify-content:center;
                align-items:center;
            "
        >

            Register

        </a>

    </div>

</div>

</body>
</html>