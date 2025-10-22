<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// SQLite-Datenbank in textdateien/mitteilungen.db
$dbFile = __DIR__ . DIRECTORY_SEPARATOR . 'textdateien' . DIRECTORY_SEPARATOR . 'mitteilungen.db';
$dsn = 'sqlite:' . $dbFile;
$mitteilungen = [];

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tabelle erstellen, falls nicht vorhanden
    $createSql = "CREATE TABLE IF NOT EXISTS mitteilungen (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        username TEXT NOT NULL,
        titel TEXT NOT NULL,
        nachricht TEXT NOT NULL
    )";
    $pdo->exec($createSql);

    // Neue Mitteilung speichern
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['titel'])) {
        $username = $_SESSION['username'];
        $titel = trim($_POST['titel']);
        $nachricht = trim($_POST['nachricht']);

        if ($titel !== '' && $nachricht !== '') {
            $stmt = $pdo->prepare('INSERT INTO mitteilungen (username, titel, nachricht) VALUES (:username, :titel, :nachricht)');
            $stmt->execute([
                ':username' => $username,
                ':titel' => $titel,
                ':nachricht' => $nachricht
            ]);

            // Redirect um Doppel-Submit zu verhindern
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Bestehende Mitteilungen laden (neueste zuerst)
    $stmt = $pdo->query('SELECT id, created_at, username, titel, nachricht FROM mitteilungen ORDER BY created_at DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $mitteilungen[] = [
            'datum' => date('d.m.Y H:i', strtotime($row['created_at'])),
            'autor' => $row['username'],
            'titel' => $row['titel'],
            'nachricht' => $row['nachricht']
        ];
    }

} catch (Exception $e) {
    // Bei Fehlern: Fallback leer lassen und evtl. Fehler in Log schreiben
    error_log('Mitteilungen DB-Fehler: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitteilungen</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/mitteilungen.css">
</head>
<body>
    <div class="mitteilungen-container">
        <h1>Mitteilungen</h1>

        <button class="btn" id="neueBtn">Neue Mitteilung</button>

        <div class="mitteilungen-liste">
            <?php if (empty($mitteilungen)): ?>
                <p class="leer">Keine Mitteilungen vorhanden.</p>
            <?php else: ?>
                <?php foreach ($mitteilungen as $m): ?>
                    <div class="mitteilung">
                        <strong><?php echo htmlspecialchars($m['titel']); ?></strong> 
                        (<?php echo htmlspecialchars($m['datum']); ?>) von <em><?php echo htmlspecialchars($m['autor']); ?></em><br>
                        <span><?php echo htmlspecialchars($m['nachricht']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Popup -->
        <div id="popupForm" class="popup">
            <div class="popup-content">
                <span class="close">&times;</span>
                <h2>Mitteilung erstellen</h2>
                <form method="POST" action="">
                    <label>Titel:</label><br>
                    <input type="text" name="titel" placeholder="Titel" required><br><br>
                    <label>Nachricht:</label><br>
                    <textarea name="nachricht" placeholder="Nachricht eingeben" rows="4" required></textarea><br><br>
                    <button type="submit" class="btn">Speichern</button>
                </form>
            </div>
        </div>

        <div class="bottom-nav">
            <a href="dashboard.php" class="nav-btn">Start</a>
            <a href="stundenplan.php" class="nav-btn">Stundenplan</a>
            <a href="mitteilungen.php" class="nav-btn active">Mitteilungen</a>
            <a href="einstellungen.php" class="nav-btn">Einstellungen</a>
        </div>
    </div>

    <script>
        const popup = document.getElementById("popupForm");
        const btn = document.getElementById("neueBtn");
        const span = document.getElementsByClassName("close")[0];

        btn.onclick = () => { popup.style.display = "block"; }
        span.onclick = () => { popup.style.display = "none"; }
        window.onclick = (event) => { if (event.target == popup) popup.style.display = "none"; }
    </script>
</body>
</html>
