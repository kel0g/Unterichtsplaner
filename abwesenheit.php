<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Verwende die users DB als gemeinsame DB für 1:N Verbindung
$dbFile = __DIR__ . DIRECTORY_SEPARATOR . 'textdateien' . DIRECTORY_SEPARATOR . 'users.db';
$dsn = 'sqlite:' . $dbFile;
$abwesenheiten = [];

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Foreign keys aktivieren
    $pdo->exec('PRAGMA foreign_keys = ON');

    // Tabelle erstellen, falls nicht vorhanden (user_id referenziert users.id)
    $createSql = "CREATE TABLE IF NOT EXISTS abwesenheiten (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        user_id INTEGER,
        datum TEXT NOT NULL,
        startzeit TEXT NOT NULL,
        endzeit TEXT NOT NULL,
        grund TEXT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    $pdo->exec($createSql);

    // Neue Abwesenheit speichern
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $start = trim($_POST['startzeit'] ?? '');
        $end = trim($_POST['endzeit'] ?? '');
        $grund = trim($_POST['grund'] ?? '');
        $username = $_SESSION['username'] ?? null;
        $datum = date('Y-m-d');

        if ($start !== '' && $end !== '' && $grund !== '') {
            // user_id ermitteln
            $userId = null;
            if ($username) {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
                $stmt->execute([':username' => $username]);
                $u = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($u) $userId = (int)$u['id'];
            }

            $stmt = $pdo->prepare('INSERT INTO abwesenheiten (user_id, datum, startzeit, endzeit, grund) VALUES (:user_id, :datum, :start, :end, :grund)');
            $stmt->execute([
                ':user_id' => $userId,
                ':datum' => $datum,
                ':start' => $start,
                ':end' => $end,
                ':grund' => $grund
            ]);

            // Redirect um Doppel-Submit zu verhindern
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Bestehende Abwesenheiten laden (neueste zuerst) mit Join auf users
    $stmt = $pdo->query('SELECT a.id, a.created_at, a.user_id, a.datum, a.startzeit, a.endzeit, a.grund, u.username FROM abwesenheiten a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $abwesenheiten[] = [
            'name' => $row['username'] ?? 'Unbekannt',
            'datum' => $row['datum'],
            'start' => $row['startzeit'],
            'end' => $row['endzeit'],
            'grund' => $row['grund']
        ];
    }

} catch (Exception $e) {
    // Bei Fehlern: Fallback leer lassen und Fehler ins Log schreiben
    error_log('Abwesenheiten DB-Fehler: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abwesenheit</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/abwesenheit.css">
</head>
<body>
    <div class="abwesenheit-container">
        <h1>Abwesenheiten</h1>

        <button class="btn" id="neueBtn">Neue Abwesenheit eintragen</button>

        <div class="abwesenheiten-liste">
            <?php if (empty($abwesenheiten)): ?>
                <p class="leer">Keine Abwesenheiten vorhanden.</p>
            <?php else: ?>
                <?php foreach ($abwesenheiten as $a): ?>
                    <div class="abwesenheit">
                        <strong><?php echo htmlspecialchars($a['name']); ?></strong> 
                        (<?php echo htmlspecialchars($a['datum']); ?>)<br>
                        <?php echo htmlspecialchars($a['start']); ?> - <?php echo htmlspecialchars($a['end']); ?><br>
                        Grund: <?php echo htmlspecialchars($a['grund']); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Popup -->
        <div id="popupForm" class="popup">
            <div class="popup-content">
                <span class="close">&times;</span>
                <h2>Abwesenheit eintragen</h2>
                <form method="POST" action="">
                    <label>Startzeit:</label><br>
                    <input type="time" name="startzeit" required><br><br>
                    <label>Endzeit:</label><br>
                    <input type="time" name="endzeit" required><br><br>
                    <label>Grund:</label><br>
                    <input type="text" name="grund" placeholder="Grund eingeben" required><br><br>
                    <button type="submit" class="btn">Speichern</button>
                </form>
            </div>
        </div>

        <div class="bottom-nav">
            <a href="dashboard.php" class="nav-btn active">Start</a>
            <a href="stundenplan.php" class="nav-btn">Stundenplan</a>
            <a href="mitteilungen.php" class="nav-btn">Mitteilungen</a>
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