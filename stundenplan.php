<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$stundenFile = "textdateien/stunden.txt";
$aufgabenFile = "textdateien/schulaufgaben.txt";

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

// Stunden eintragen
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['fach'])) {
    $fach = htmlspecialchars(trim($_POST['fach']));
    $tag = htmlspecialchars(trim($_POST['tag']));
    $text = htmlspecialchars(trim($_POST['text']));

    if (!empty($fach) && !empty($tag) && !empty($zeit)) {
        $data = "$tag |  $fach | $text" . PHP_EOL;
        file_put_contents($aufgabenFile, $data, FILE_APPEND | LOCK_EX);
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
    <title>Stundenplan</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/stundenplan.css">
</head>
<body>
    <div class="stundenplan-container">
        <h1>Stundenplan</h1>

        <button class="btn" id="neueBtn">+</button>

        <form method="POST" action="" class="stunden-form">
            <select name="tag" required>
                <option value="">Tag wählen</option>
                <?php foreach ($tage as $t): ?>
                    <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                <?php endforeach; ?>
            </select>

        <from class="dropdown" id="options">
            <input list="moglichkeiten" type="text" name="optionen"/>
            <datalist id="option">
                <option value="Stunden" id="wahl2"/>
                <option value="Schulaufgabe" id="wahl1"/>
            </datalist>
        </form>


        <div id="popupForm1" class="popup">
            <div class="popup-content">
                <span class="close">&times;</span>
                <form method="POST" action="">
                <h1>Schulaufgaben</h1>
                <input type="time" name="zeit" required>
                <input type="text" name="fach" placeholder="Fach" required>
                <button type="submit" class="btn">Eintragen</button>
                </form>
            </div>
        </div>

         <div id="popupForm2" class="popup">
            <div class="popup-content">
                <span class="close">&times;</span>
                <form method="POST" action="">
                <h1>Stunden</h1>
                <input type="time" name="zeit" required>
                <input type="text" name="fach" placeholder="Fach" required>
                <input type="text" name="text" placeholder="Sachinhalt" required>
                <button type="submit" class="btn">Eintragen</button>
                </form>
            </div>
        </div>

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
            <a href="einstellungen.php" class="nav-btn">Einstellungen</a>
        </div>
    </div>

     <script>
        const auswahl = document.getElementById("options");
        const wahl1 = document.getElementById("wahl1");
        const wahl2 = document.getElementById("wahl2");
        const popup1 = document.getElementById("popupForm1");
        const popup2 = document.getElementById("popupForm2");
        const btn = document.getElementById("neueBtn");
        const closes = document.querySelectorAll(".popup .close");

        btn.onclick = () => { auswahl.style.display = "block"; }
        wahl1.onclick = () => { popup1.style.display = "block"; }
        wahl2.onclick = () => { popup2.style.display = "block"; }
        closes.forEach(c => c.addEventListener("click", e => {
            e.target.closest(".popup").style.display = "none";
        }));

        window.addEventListener("click", e => {
            if (e.target.classList.contains("popup")) e.target.style.display = "none";
        });
    </script>
</body>
</html>
