<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$stundenFile = "stunden.txt";
$aufgabenFile = "schulaufgaben.txt";

// Stunden eintragen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['fach'])) {
    $fach = htmlspecialchars(trim($_POST['fach']));
    $tag = htmlspecialchars(trim($_POST['tag']));
    $zeit = htmlspecialchars(trim($_POST['zeit']));

    if (!empty($fach) && !empty($tag) && !empty($zeit)) {
        $data = "$tag | $zeit | $fach" . PHP_EOL;
        file_put_contents($stundenFile, $data, FILE_APPEND | LOCK_EX);
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stundenplan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="stundenplan.css">
</head>
<body>
    <div class="stundenplan-container">
        <h1>Stundenplan</h1>

        <form method="POST" action="" class="stunden-form">
            <select name="tag" required>
                <option value="">Tag wählen</option>
                <?php foreach ($tage as $t): ?>
                    <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                <?php endforeach; ?>
            </select>

            <input type="time" name="zeit" required>
            <input type="text" name="fach" placeholder="Fach" required>
            <button type="submit" class="btn">Eintragen</button>
        </form>

        <div class="kalender">
            <?php foreach ($tage as $tag): ?>
                <div class="tag-spalte">
                    <h3><?php echo $tag; ?></h3>
                    <div class="stunden-liste">
                        <?php
                        $hasEntries = false;
                        foreach ($stunden as $s) {
                            if ($s['tag'] === $tag) {
                                $hasEntries = true;
                                $isAufgabe = false;
                                foreach ($aufgaben as $a) {
                                    if ($a['tag'] === $tag && strtolower($a['fach']) === strtolower($s['fach'])) {
                                        $isAufgabe = true;
                                        break;
                                    }
                                }
                                echo '<div class="stunde ' . ($isAufgabe ? 'aufgabe' : '') . '">';
                                echo '<strong>' . htmlspecialchars($s['fach']) . '</strong><br>';
                                echo '<span>' . htmlspecialchars($s['zeit']) . '</span>';
                                echo '</div>';
                            }
                        }
                        if (!$hasEntries) {
                            echo '<p class="leer">Keine Einträge</p>';
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bottom-nav">
            <a href="dashboard.php" class="nav-btn">Start</a>
            <a href="stundenplan.php" class="nav-btn active">Stundenplan</a>
            <a href="mitteilungen.php" class="nav-btn">Mitteilungen</a>
        </div>
    </div>
</body>
</html>
