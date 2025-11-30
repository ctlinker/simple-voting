<?php
    use DB\Database;
    $error = "";
    $sql = "SELECT results_visible FROM admin_settings WHERE id = 1";
    $results_visible = Database::fetch($sql)["results_visible"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php if ($results_visible): ?>
        <h2>Resultat Des Elections</h2>
        <?php
            $results = Database::fetchAll(
                sql: "SELECT c.name, COUNT(v.id) as vote_count FROM candidates c LEFT JOIN votes v ON c.id = v.candidate_id GROUP BY c.id ORDER BY vote_count DESC",
            );
            foreach ($results as $row) {
                echo "<p>" .
                    htmlspecialchars($row["name"]) .
                    ": " .
                    $row["vote_count"] .
                    " votes</p>";
            }
        ?>
    <?php else: ?>
        <h2>Les Resultat ne sont pas encore disponible</h2>
    <?php endif ?>

    
</body>
</html>