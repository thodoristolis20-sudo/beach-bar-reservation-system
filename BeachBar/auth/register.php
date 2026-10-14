<?php
session_start();

require_once "../config/db.php";

$message = "";
$messageType = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $username =
        trim($_POST["username"]);

    $email =
        trim($_POST["email"]);

    $password =
        password_hash(
            $_POST["password"],
            PASSWORD_DEFAULT
        );

    $sql = "
        INSERT INTO users
        (
            username,
            email,
            password
        )
        VALUES (?, ?, ?)
    ";

    $stmt = $pdo->prepare($sql);

    try {

        $stmt->execute([

            $username,
            $email,
            $password

        ]);

        $message =
            "Registration successful";

        $messageType =
            "auth-success";

    } catch(PDOException $e) {

        $message =
            "Username or email already exists";

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

    <title>Register</title>

    <link rel="stylesheet"
          href="../assets/style.css">

</head>

<body>

<div class="auth-container">

    <h2>Create Account</h2>

    <p class="auth-subtitle">
        Register a new user
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
            type="text"
            name="username"
            placeholder="Username"
            required>

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

            Register

        </button>

    </form>

    <div class="auth-footer">

        Already have an account?

        <a href="login.php">

            Login

        </a>

    </div>

</div>

</body>
</html>