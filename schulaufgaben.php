<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$aufgabenFile = "schulaufgaben.txt";
$aufgaben = [];

if (file_exists($aufgabenFile)) {
    $lines = file($aufgabenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Format: Tag | Fach | Beschreibung
        $teile = explode("|", $line);
        if (count($teile) >= 3) {
            $aufgaben[] = [
                "tag" => trim($teile[0]),
                "fach" => trim($teile[1]),
                "beschreibung" => trim($teile[2])
            ];
        }
    }
}

// Bestehende Stunden laden
$stunden = [];
if (file_exists($stundenFile)) {
    $lines = file($stundenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        list($tag, $zeit, $fach) = array_map('trim', explode("|", $line));
        $stunden[] = ["tag" => $tag, "zeit" => $zeit, "fach" => $fach];
    }
}

// Bestehende Schulaufgaben laden (für gelbe Markierung)
$aufgaben = [];
if (file_exists($aufgabenFile)) {
    $lines = file($aufgabenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Format: Tag | Fach | Beschreibung ...
        $teile = explode("|", $line);
        if (count($teile) >= 2) {
            $aufgaben[] = ["tag" => trim($teile[0]), "fach" => trim($teile[1])];
        }
    }
}

$tage = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag"];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schulaufgaben</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="schulaufgaben.css">
</head>
<body>
    <div class="schulaufgaben-container">
        <h1>Schulaufgaben</h1>

        <?php if (empty($aufgaben)): ?>
            <p class="leer">Keine Schulaufgaben vorhanden.</p>
        <?php else: ?>
            <div class="aufgaben-liste">
                <?php foreach ($aufgaben as $a): ?>
                    <div class="aufgabe">
                        <strong><?php echo htmlspecialchars($a['fach']); ?></strong> 
                        (<?php echo htmlspecialchars($a['tag']); ?>)<br>
                        <span><?php echo htmlspecialchars($a['beschreibung']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="bottom-nav">
            <a href="dashboard.php" class="nav-btn">Start</a>
            <a href="stundenplan.php" class="nav-btn">Stundenplan</a>
            <a href="mitteilungen.php" class="nav-btn">Mitteilungen</a>
        </div>
    </div>
</body>
</html>
