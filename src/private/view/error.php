<?php
session_start();
// Check if an error exists in the session
$error = isset($_SESSION[""]) ? $_SESSION[""] : null;
// Clear the error from the session
unset($_SESSION[""]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="/style/error.css">
</head>
<body>
    <div class="error-container">
        <h1>An Error Occurred</h1>
        <p>
            <?php if ($error): ?>
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            <?php else: ?>
                No error details are available.
            <?php endif; ?>
        </p>
        <a href="/">Go Back to Home</a>
    </div>
</body>
</html>
