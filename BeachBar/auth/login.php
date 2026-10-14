<?php
session_start();

require_once "../config/db.php";

$message = "";
$messageType = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $email =
        trim($_POST["email"]);

    $password =
        $_POST["password"];

    $sql = "
        SELECT *
        FROM users
        WHERE email = ?
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if(
        $user &&
        password_verify(
            $password,
            $user["password"]
        )
    ) {

        $_SESSION["user_id"] =
            $user["id"];

        $_SESSION["username"] =
            $user["username"];

        header(
            "Location: ../pages/dashboard.php"
        );

        exit;

    } else {

        $message =
            "Invalid email or password";

        $messageType =
            "auth-error";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <link rel="stylesheet"
          href="../assets/style.css">

</head>

<body>

<div class="auth-container">

    <h2>Welcome Back</h2>

    <p class="auth-subtitle">
        Login to continue
    </p>

    <?php if($message != ""): ?>

        <div
            class="
                auth-message
                <?php echo $messageType; ?>
            "
        >

            <?php echo $message; ?>

        </div>

    <?php endif; ?>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Email"
            required>

        <input
            type="password"
            name="password"
            placeholder="Password"
            required>

        <button type="submit">

            Login

        </button>

    </form>

    <div class="auth-footer">

        Don’t have an account?

        <a href="register.php">

            Register

        </a>

    </div>

</div>

</body>
</html>