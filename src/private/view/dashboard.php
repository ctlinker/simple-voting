<?php
    use DB\Database;
    use Utils\Error;
    use Dompdf\Dompdf;

    if(!isset($_SESSION["user_id"])){
        Error::invoque("Vous n'ete pas autoriser a voir cette page");
    }

    // LOGIC: Generate Tokens (Helper for the admin)
    if (isset($_POST["generate_tokens"])) {
        for ($i = 0; $i < 10; $i++) {
            $code = strtoupper(bin2hex(random_bytes(4))); // Generates random string like 1A2B3C4D
            Database::insert("tokens", ["code" => $code]);
        }
    }

    if (isset($_POST["export_tokens_pdf"])) {
        $dompdf = new Dompdf();
        $html = "<h1>Token List</h1>";
        $html .=
            "<table border='1' style='width:100%; border-collapse:collapse; text-align:center; padding:8px;'>";
        $html .=
            "<thead><tr><th>#</th><th>Token</th><th>Status</th></tr></thead><tbody>";

        $tokens = Database::fetchAll("SELECT * FROM tokens ORDER BY id ASC");
        foreach ($tokens as $index => $token) {
            $status = $token["is_used"] ? "USED" : "ACTIVE";
            $html .= "<tr>";
            $html .= "<td style='padding:8px;'>" . ($index + 1) . "</td>";
            $html .= "<td style='padding:8px;'>" . $token["code"] . "</td>";
            $html .= "<td style='padding:8px;'>" . $status . "</td>";
            $html .= "</tr>";
        }

        $html .= "</tbody></table>";

        $dompdf->loadHtml($html);
        $dompdf->setPaper("A4", "portrait");
        $dompdf->render();
        $dompdf->stream("tokens.pdf", ["Attachment" => true]);
        exit();
    }

    // LOGIC: Toggle Result Visibility
    if (isset($_POST["toggle_results_visibility"])) {
        $currentVisibility = Database::fetch(
            "SELECT results_visible FROM admin_settings WHERE id = 1",
        )["results_visible"];
        $newVisibility = $currentVisibility ? 0 : 1;
        Database::execQuery(
            "UPDATE admin_settings SET results_visible = ? WHERE id = 1",
            [$newVisibility],
        );
    }

    // LOGIC: Calculate Results
    $sql = "SELECT c.name, COUNT(v.id) as vote_count
            FROM candidates c
            LEFT JOIN votes v ON c.id = v.candidate_id
            GROUP BY c.id
            ORDER BY vote_count DESC";
    
    $results = Database::fetchAll($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        .bar-container { background: #eee; width: 50%; height: 20px; }
        .bar { background: #4CAF50; height: 100%; text-align: center; color: white;}
    </style>
</head>
<body>
    <h1>Election Results</h1>

    <table>
        <?php
        // Calculate total for percentage
        $total_votes = array_sum(array_column($results, "vote_count"));

        foreach ($results as $row):
            $width =
                $total_votes > 0
                    ? ($row["vote_count"] / $total_votes) * 100
                    : 0; ?>
        <tr>
            <td width="150"><?= htmlspecialchars($row["name"]) ?></td>
            <td>
                <div class="bar-container">
                    <div class="bar" style="width: <?= $width ?>%;">
                        <?= $row["vote_count"] ?>
                    </div>
                </div>
            </td>
        </tr>
        <?php
        endforeach;
        ?>
    </table>

    <hr>

    <h3>Token Management</h3>
    <form method="POST" action="/api.php/?request=admin_dashboard">
        <button type="submit" name="generate_tokens">Generate 10 New Random Tokens</button>
    </form>
    <form method="POST" action="/api.php/?request=admin_dashboard" style="margin-top: 10px;">
        <button type="submit" name="export_tokens_pdf">Export Tokens as PDF</button>
    </form>

    <h4>Existing Tokens:</h4>
    <ul>
        <?php
        $tokens = Database::fetchAll(sql: "SELECT * FROM tokens ORDER BY id DESC LIMIT 20");

        foreach ($tokens as $t) {
            $status = $t["is_used"] ? "USED" : "ACTIVE";
            echo "<li>{$t["code"]} - <strong>$status</strong></li>";
        }
        ?>
    </ul>

    <hr>

    <h3>Results Visibility</h3>
    <form method="POST" action="/api.php/?request=admin_dashboard">
        <?php
        $visibility = Database::fetch(
            "SELECT results_visible FROM admin_settings WHERE id = 1",
        )["results_visible"];
        $buttonText = $visibility ? "Hide Results" : "Show Results";
        ?>
        <button type="submit" name="toggle_results_visibility"><?= $buttonText ?></button>
    </form>
</body>
</html>
