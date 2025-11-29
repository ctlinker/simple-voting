<?php
session_start();
use DB\Database;

if (!isset($_SESSION['valid_token_id']) || !isset($_POST['candidate_id'])) {
    header("Location: index.php");
    exit;
}

$token_id     = $_SESSION['valid_token_id'] ?? null;
$candidate_id = $_POST['candidate_id'] ?? null;

if (!$token_id || !$candidate_id) {
    throw new Exception("Missing token or candidate ID");
}

try {

    Database::useTransaction(function (Database $DB, PDO $pdo) use ($token_id, $candidate_id) {

        // 1. Lock the token row
        $token = $DB::fetch(
            "SELECT is_used FROM tokens WHERE id = ? FOR UPDATE",
            [$token_id]
        );

        if (!$token) {
            throw new Exception("Token not found!");
        }

        if ($token['is_used'] == 1) {
            throw new Exception("Token already used!");
        }

        // 2. Mark token as used
        $DB::update(
            "tokens",
            ["is_used" => 1],
            "id = ?",
            [$token_id]
        );

        // 3. Insert the vote
        $DB::insert("votes", [
            "candidate_id" => $candidate_id,
            "token_id"     => $token_id
        ]);

    });

    echo "Vote successfully registered!";
    echo "<h1>Thank you! Your vote has been recorded.</h1><a href='index.php'>Back to Home</a>";

} catch (Exception $e) {
    // Safe rollback happens automatically in useTransaction
    echo "Failed to register vote: " . $e->getMessage();
}

session_destroy();

?>