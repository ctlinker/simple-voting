<?php
session_start();
use DB\Database;

if (!isset($_SESSION["valid_token_id"]) || !isset($_POST["candidate_id"])) {
    header("Location: index.php");
    exit();
}

$token_id = $_SESSION["valid_token_id"] ?? null;
$candidate_id = $_POST["candidate_id"] ?? null;
$html = "";

if (!$token_id || !$candidate_id) {
    throw new Exception("Missing token or candidate ID");
}

try {
    Database::useTransaction(function ($DB, PDO $pdo) use (
        $token_id,
        $candidate_id,
    ) {
        // 1. Lock the token row
        $token = $DB::fetch(
            "SELECT is_used FROM tokens WHERE id = ? FOR UPDATE",
            [$token_id],
        );

        if (!$token) {
            throw new Exception("Token not found!");
        }

        if ($token["is_used"] == 1) {
            throw new Exception("Token already used!");
        }

        // 2. Mark token as used
        $DB::update("tokens", ["is_used" => 1], "id = ?", [$token_id]);

        // 3. Insert the vote
        $DB::insert("votes", [
            "candidate_id" => $candidate_id,
            "token_id" => $token_id,
        ]);
    });

    $html .= "<div class='success-container'>"
    . "<h1>Merci ! Votre vote a été enregistré.</h1>";

} catch (Exception $e) {
    // Safe rollback happens automatically in useTransaction
    $html .= "<div class='error-container'>";
    $html .= "<h1>Échec de l'enregistrement du vote</h1>";
    $html .= "<p>" .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8") .
        "</p>";
}

$html  .=  "<a href='/' class='back-button'>Retour à l'accueil</a> </div>";

session_destroy();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote</title>
    <link rel="stylesheet" href="/style/submit.css">
</head>
<body>
    <?= $html ?>
</body>
</html>