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
    header("Location: /index.html");
    exit();
}

// Fetch candidates
$token_id = $_SESSION["valid_token_id"];
$candidates = Database::fetchAll("SELECT * FROM candidates");
?>

<!DOCTYPE html>
<html>
<head><title>Cast Your Vote</title></head>
<body>
    <h2>Select a Candidate</h2>
    <form action="/api.php/?request=submit_vote" method="POST" onsubmit="document.getElementById('token_id').value = <?= $token_id ?>;">
        <?php foreach ($candidates as $candidate): ?>
            <div class="candidate-card">
                <label>
                    <input type="radio" name="candidate_id" value="<?= $candidate[
                        "id"
                    ] ?>" required>
                    <strong><?= htmlspecialchars($candidate["name"]) ?></strong>
                </label>
            </div>
        <?php endforeach; ?>
        <input type="hidden" id="token_id" hidden name="token_id" value="">
        <button type="submit" onclick="return confirm('Are you sure?')">Submit Vote</button>
    </form>
</body>
</html>
