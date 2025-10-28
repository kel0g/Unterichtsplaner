<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$aufgabenFile = "textdateien/schulaufgaben.txt";
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
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schulaufgaben</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/schulaufgaben.css">
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
            <a href="dashboard.php" class="nav-btn active">Start</a>
            <a href="stundenplan.php" class="nav-btn">Stundenplan</a>
            <a href="mitteilungen.php" class="nav-btn">Mitteilungen</a>
            <a href="einstellungen.php" class="nav-btn">Einstellungen</a>
        </div>
    </div>
</body>
</html>
