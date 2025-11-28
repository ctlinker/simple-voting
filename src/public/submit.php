<?php
session_start();
require 'db.php';

if (!isset($_SESSION['valid_token_id']) || !isset($_POST['candidate_id'])) {
    header("Location: index.php");
    exit;
}

$token_id = $_SESSION['valid_token_id'];
$candidate_id = $_POST['candidate_id'];

try {
    // 1. Start Transaction
    $pdo->beginTransaction();

    // 2. Lock the token row to ensure no one else is using it right now
    // "FOR UPDATE" locks this row until the transaction commits
    $stmt = $pdo->prepare("SELECT is_used FROM tokens WHERE id = ? FOR UPDATE");
    $stmt->execute([$token_id]);
    $token = $stmt->fetch();

    if ($token['is_used'] == 1) {
        // If somehow it got used in the split second between pages
        throw new Exception("Token already used!");
    }

    // 3. Mark token as used
    $updateStmt = $pdo->prepare("UPDATE tokens SET is_used = 1 WHERE id = ?");
    $updateStmt->execute([$token_id]);

    // 4. Insert the vote
    $voteStmt = $pdo->prepare("INSERT INTO votes (candidate_id, token_id) VALUES (?, ?)");
    $voteStmt->execute([$candidate_id, $token_id]);

    // 5. Commit Transaction
    $pdo->commit();

    // Destroy session so they can't go back
    session_destroy();
    echo "<h1>Thank you! Your vote has been recorded.</h1><a href='index.php'>Back to Home</a>";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>