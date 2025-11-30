<?php
use DB\Database;
use Utils\Error;
use Dompdf\Dompdf;

if (!isset($_SESSION["user_id"])) {
    Error::invoque("Vous n'êtes pas autorisé à voir cette page");
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

// LOGIC: Add Candidate
if (isset($_POST["add_candidate"])) {
    $candidateName = $_POST["candidate_name"];
    $candidatePhoto = null;

    if (!empty($_FILES["candidate_photo"]["name"])) {
        $photoPath = "/uploads/" . basename($_FILES["candidate_photo"]["name"]);
        move_uploaded_file(
            $_FILES["candidate_photo"]["tmp_name"],
            __DIR__ . "/../../public" . $photoPath,
        );
        $candidatePhoto = $photoPath;
    }

    Database::insert("candidates", [
        "name" => $candidateName,
        "photo" => $candidatePhoto,
    ]);
}

// LOGIC: Remove Candidate
if (isset($_POST["remove_candidate"])) {
    $candidateId = $_POST["candidate_id"];
    Database::delete("candidates", "id = ?", [$candidateId]);
}

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
    <link rel="stylesheet" href="/style/card.css">
    <link rel="stylesheet" href="/style/sidebar.css">
    <link rel="stylesheet" href="/style/dashboard.css">
</head>

<body>
    <div class="sidebar-container">
        <div class="sidebar">
            <button class="sidebar-btn current" data-view="token">
                Token
            </button>

            <button class="sidebar-btn" data-view="candidat">
                Candidat
            </button>

            <button class="sidebar-btn" data-view="resultat">
                Résultat
            </button>
        </div>
        <div class="sidebar-content">
            <div class="sidebar-panel visible" data-view="token">
                <div class="card">

                    <h3>Token Management</h3>

                    <form method="POST" action="/api.php/?request=admin_dashboard" class="token-form">
                        <div class="form-group">
                            <label for="token_count">Nombre de tokens à générer :</label>
                            <input type="number" id="token_count" name="token_count" min="1" value="10" required>
                        </div>
                        <button type="submit" name="generate_tokens" class="btn-primary">Générer</button>
                    </form>

                    <form method="POST" action="/api.php/?request=admin_dashboard" class="token-form">
                        <div class="form-group">
                            <label for="export_count">Nombre de tokens à exporter :</label>
                            <input type="number" id="export_count" name="export_count" min="1" value="10" required>
                        </div>
                        <button type="submit" name="export_tokens_pdf" class="btn-primary">Exporter en PDF</button>
                    </form>

                    <h4>Existing Tokens:</h4>
                    <ul class="token-list">
                        <?php
                        $tokens = Database::fetchAll(
                            sql: "SELECT * FROM tokens ORDER BY id DESC LIMIT 20",
                        );

                        foreach ($tokens as $t) {
                            $status = $t["is_used"] ? "USED" : "ACTIVE";
                            echo "<li class='token-item'><span class='token-code'>{$t["code"]}</span> - <strong class='token-status $status'>$status</strong></li>";
                        }
                        ?>
                    </ul>

                </div>
            </div>

            <div class="sidebar-panel" data-view="candidat">
                <div class="card">
                    <h2>Gestion des Candidats</h3>

                    <h3>Ajouter un Candidat</h3>
                    <div class="add-candidate">
                        <form method="POST" action="/api.php/?request=admin_dashboard"
                            enctype="multipart/form-data">
                            <label for="candidate_name">Nom du Candidat:</label>
                            <input type="text" id="candidate_name" name="candidate_name" required>
                            <label for="candidate_photo">Photo du Candidat (optionnel):</label>
                            <input type="file" id="candidate_photo" name="candidate_photo" accept="image/*">
                            <button type="submit" name="add_candidate">Ajouter</button>
                        </form>
                    </div>

                    <h3>Candidat Existant</h3>
                    <div class="candidate-management">
                        <ul class="candidate-list">
                            <?php
                            $candidates = Database::fetchAll(
                                "SELECT * FROM candidates ORDER BY id ASC",
                            );
                            foreach ($candidates as $candidate): ?>
                                <li class="candidate-item">
                                    <form method="POST" action="/api.php/?request=admin_dashboard"
                                        class="remove-candidate-form">
                                        <input type="hidden" name="candidate_id" value="<?= $candidate[
                                            "id"
                                        ] ?>">
                                        <button type="submit" name="remove_candidate"
                                            class="remove-candidate-btn">Supprimer</button>
                                    </form>
                                    <span class="candidate-name"><?= htmlspecialchars(
                                        $candidate["name"],
                                        ENT_QUOTES,
                                        "UTF-8",
                                    ) ?></span>
                                </li>
                            <?php endforeach;
                            ?>
                        </ul>
                    </div>

                </div>
            </div>

            <div class="sidebar-panel" data-view="resultat">
                <div class="card">

                    <h1>
                        Gestion Des Resultat
                    </h1>

                    <h2>
                        Visibilité des Résultats
                    </h2>
                    <form method="POST" action="/api.php/?request=admin_dashboard">
                        <?php
                        $sql =
                            "SELECT results_visible FROM admin_settings WHERE id = 1";
                        $visibility = Database::fetch($sql)["results_visible"];

                        $buttonText = $visibility
                            ? "Hide Results"
                            : "Show Results";
                        ?>
                        <button type="submit" name="toggle_results_visibility">
                            <?= $buttonText ?>
                        </button>
                    </form>


                    <h2>Election Results</h2>


                    <table>
                        <?php
                        // Calculate total for percentage
                        $total_votes = array_sum(
                            array_column($results, "vote_count"),
                        );

                        foreach ($results as $row):
                            $width =
                                $total_votes > 0
                                    ? ($row["vote_count"] / $total_votes) * 100
                                    : 0; ?>
                            <tr>
                                <td width="150"><?= htmlspecialchars(
                                    $row["name"],
                                ) ?></td>
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
                </div>
            </div>
        </div>
        <script>
            const buttons = document.querySelectorAll(".sidebar-btn");
            const panels = document.querySelectorAll(".sidebar-panel");

            buttons.forEach(btn => {
                btn.addEventListener("click", () => {
                    const view = btn.dataset.view;

                    // Update active button
                    buttons.forEach(b => b.classList.remove("current"));
                    btn.classList.add("current");

                    // Update visible content
                    panels.forEach(p => {
                        p.classList.toggle("visible", p.dataset.view === view);
                    });
                });
            });
        </script>
    </div>
</body>

</html>
