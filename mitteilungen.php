<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$mitteilungenFile = "mitteilungen.txt";
$mitteilungen = [];

// Neue Mitteilung speichern
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['titel'])) {
    $username = $_SESSION['username'];
    $titel = htmlspecialchars(trim($_POST['titel']));
    $nachricht = htmlspecialchars(trim($_POST['nachricht']));
    $datum = date("d.m.Y H:i");

    if (!empty($titel) && !empty($nachricht)) {
        $eintrag = "$datum | $username | $titel | $nachricht" . PHP_EOL;
        file_put_contents($mitteilungenFile, $eintrag, FILE_APPEND | LOCK_EX);
    }
}

// Bestehende Mitteilungen laden
if (file_exists($mitteilungenFile)) {
    $lines = file($mitteilungenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $teile = explode("|", $line);
        if (count($teile) >= 4) {
            $mitteilungen[] = [
                "datum" => trim($teile[0]),
                "autor" => trim($teile[1]),
                "titel" => trim($teile[2]),
                "nachricht" => trim($teile[3])
            ];
        }
    }
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
                <?php foreach (array_reverse($mitteilungen) as $m): ?>
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
