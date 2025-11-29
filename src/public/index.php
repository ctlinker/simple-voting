<?php
require_once "deps.php";
use DB\Database;
session_start();


$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code = trim($_POST["token"]);

    // Check if token exists and is not used
    $token = Database::fetch("SELECT * FROM tokens WHERE code = ?", [$code]);

    if ($token && $token["is_used"] == 0) {
        $_SESSION["valid_token_id"] = $token["id"]; // Save ID to session
        header("Location: vote.php");
        exit();
    } elseif ($token && $token["is_used"] == 1) {
        $error = "This token has already been used.";
    } else {
        $error = "Invalid token.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Vote Login</title>
</head>

<body>
    <h2>Enter your Voting Token</h2>
    <?php if ($error) {
        echo "<p style='color:red'>$error</p>";
    } ?>
    <form method="POST">
        <input type="text" name="token" required placeholder="Ex: ABC-123">
        <button type="submit">Enter</button>
    </form>
</body>

</html>