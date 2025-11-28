<?php
require __DIR__ . "/../../vendor/autoload.php";
use DB\Database;

// LOGIC: Calculate Results
$sql = "SELECT c.name, COUNT(v.id) as vote_count 
        FROM candidates c 
        LEFT JOIN votes v ON c.id = v.candidate_id 
        GROUP BY c.id 
        ORDER BY vote_count DESC";
//$results = $pdo->query($sql)->fetchAll();
$results = Database::fetchAll($sql);
// LOGIC: Generate Tokens (Helper for the admin)
if (isset($_POST['generate_tokens'])) {
    for ($i = 0; $i < 10; $i++) {
        $code = strtoupper(bin2hex(random_bytes(4))); // Generates random string like 1A2B3C4D
        Database::insert("tokens", [ "code" => $code ]);
        //$pdo->prepare("INSERT INTO tokens (code) VALUES (?)")->execute([$code]);
    }
    header("Location: admin.php"); // Refresh
}
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
        $total_votes = array_sum(array_column($results, 'vote_count')); 
        
        foreach ($results as $row): 
            $width = ($total_votes > 0) ? ($row['vote_count'] / $total_votes) * 100 : 0;
        ?>
        <tr>
            <td width="150"><?= htmlspecialchars($row['name']) ?></td>
            <td>
                <div class="bar-container">
                    <div class="bar" style="width: <?= $width ?>%;">
                        <?= $row['vote_count'] ?>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <hr>

    <h3>Token Management</h3>
    <form method="POST">
        <button type="submit" name="generate_tokens">Generate 10 New Random Tokens</button>
    </form>

    <h4>Existing Tokens:</h4>
    <ul>
        <?php 
        $tokens = Database::fetchAll("SELECT * FROM tokens ORDER BY id DESC LIMIT 20");
        // $tokens = $pdo->query("SELECT * FROM tokens ORDER BY id DESC LIMIT 20")->fetchAll();
        foreach($tokens as $t) {
            $status = $t['is_used'] ? "USED" : "ACTIVE";
            echo "<li>{$t['code']} - <strong>$status</strong></li>";
        }
        ?>
    </ul>
</body>
</html>