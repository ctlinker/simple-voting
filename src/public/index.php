<?php
require_once "deps.php";
use DB\Database;
session_start();

$error = "";
$results_visible = Database::fetch(
    "SELECT results_visible FROM admin_settings WHERE id = 1",
)["results_visible"];

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
    <?php if ($results_visible): ?>
        <h2>Election Results</h2>
        <?php
        $results = Database::fetchAll(
            "SELECT c.name, COUNT(v.id) as vote_count FROM candidates c LEFT JOIN votes v ON c.id = v.candidate_id GROUP BY c.id ORDER BY vote_count DESC",
        );
        foreach ($results as $row) {
            echo "<p>" .
                htmlspecialchars($row["name"]) .
                ": " .
                $row["vote_count"] .
                " votes</p>";
        }
        ?>
        <hr>
    <?php endif; ?>
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
