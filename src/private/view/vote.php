<?php
use DB\Database;
use Utils\Error;
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if token exists and is not used
    $code = trim($_POST["token"]);
    $token = Database::fetch("SELECT * FROM tokens WHERE code = ?", [$code]);

    if ($token && $token["is_used"] == 0) {
        $_SESSION["valid_token_id"] = $token["id"]; // Save ID to session
    } elseif ($token && $token["is_used"] == 1) {
        Error::invoque("Ce token a deja ete utiliser");
    } else {
        Error::invoque("Token non valide");
    }
} else {
    Error::invoque("Requette non valide, Expecting Post");
}

// Protect the page
if (!isset($_SESSION["valid_token_id"])) {
    header("Location: index.php");
    exit();
}

// Fetch candidates
$candidates = Database::fetchAll("SELECT * FROM candidates");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote</title>
    <link rel="stylesheet" href="/style/vote.css">
</head>
<body>
    <div class="vote-container">
        <h1>Liste des Candidats</h1>
        <ul class="candidate-list">
            <?php foreach ($candidates as $candidate): ?>
                <li class="candidate-item">
                    <span class="candidate-name"><?= htmlspecialchars(
                        $candidate["name"],
                        ENT_QUOTES,
                        "UTF-8",
                    ) ?></span>
                    <form action="/api.php/?request=submit_vote" method="POST" class="vote-form">
                        <input type="hidden" name="candidate_id" value="<?= $candidate[
                            "id"
                        ] ?>">
                        <button type="submit" class="vote-button">Voter Pour</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
