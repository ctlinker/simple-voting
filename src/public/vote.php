<?php
require_once "deps.php";
use DB\Database;
session_start();

// Protect the page
if (!isset($_SESSION['valid_token_id'])) {
    header("Location: index.php");
    exit;
}

// Fetch candidates
$candidates = Database::fetchAll("SELECT * FROM candidates");
?>

<!DOCTYPE html>
<html>
<head><title>Cast Your Vote</title></head>
<body>
    <h2>Select a Candidate</h2>
    <form action="submit.php" method="POST">
        <?php foreach ($candidates as $candidate): ?>
            <div class="candidate-card">
                <label>
                    <input type="radio" name="candidate_id" value="<?= $candidate['id'] ?>" required>
                    <strong><?= htmlspecialchars($candidate['name']) ?></strong>
                </label>
            </div>
        <?php endforeach; ?>
        <br>
        <button type="submit" onclick="return confirm('Are you sure?')">Submit Vote</button>
    </form>
</body>
</html>