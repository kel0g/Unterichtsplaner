<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$stundenFile = "textdateien/stunden.txt";
$aufgabenFile = "textdateien/schulaufgaben.txt";

// POST-Handling: Formulare unterscheiden per form_type
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $form_type = $_POST['form_type'] ?? '';

    if ($form_type === 'stunde') {
        $fach = htmlspecialchars(trim($_POST['fach'] ?? ''));
        $tag = htmlspecialchars(trim($_POST['tag'] ?? ''));
        $zeit = htmlspecialchars(trim($_POST['zeit'] ?? ''));

        if ($fach !== '' && $tag !== '' && $zeit !== '') {
            $data = "$tag | $zeit | $fach" . PHP_EOL;
            file_put_contents($stundenFile, $data, FILE_APPEND | LOCK_EX);
        }

    } elseif ($form_type === 'aufgabe') {
        // Schulaufgabe anlegen (die Stunde bleibt erhalten)
        $fach = htmlspecialchars(trim($_POST['fach'] ?? ''));
        $tag = htmlspecialchars(trim($_POST['tag'] ?? ''));
        $text = htmlspecialchars(trim($_POST['text'] ?? ''));
        $zeit = htmlspecialchars(trim($_POST['zeit'] ?? ''));

        if ($fach !== '' && $tag !== '' && $text !== '') {
            $data = "$tag | $fach | $text" . PHP_EOL;
            file_put_contents($aufgabenFile, $data, FILE_APPEND | LOCK_EX);
        }
    }

    // Redirect, um Doppel-Submits bei Reload zu vermeiden
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
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
            <select id="tagSelect" name="tag" required>
                <option value="">Tag wählen</option>
                <?php foreach ($tage as $t): ?>
                    <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Auswahl-Popup statt Dropdown -->
        <div id="selectPopup" class="popup">
            <div class="popup-content">
                <span class="close select-close">&times;</span>
                <h2>Was möchtest du eintragen?</h2>
                <div class="select-buttons">
                    <button type="button" id="openAufgabe" class="btn">Stunde</button>
                </div>
            </div>
        </div>

        <div id="popupForm1" class="popup">
            <div class="popup-content">
                <span class="close">&times;</span>
                <form method="POST" action="">
                <h1>Stunden</h1>
                <input type="hidden" name="form_type" value="stunde">
                <input type="hidden" name="tag" class="popup-tag">
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
                <h1>Schulaufgaben</h1>
                <input type="hidden" name="form_type" value="aufgabe">
                <input type="hidden" name="tag" class="popup-tag">
                <input type="hidden" name="fach" class="popup-fach">
                <input type="hidden" name="zeit" class="popup-zeit">
                <input type="text" name="fach_visible" placeholder="Fach" required>
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
                                echo '<div class="stunde ' . ($isAufgabe ? 'aufgabe' : '') . '" data-tag="' . htmlspecialchars($s['tag']) . '" data-fach="' . htmlspecialchars($s['fach']) . '" data-zeit="' . htmlspecialchars($s['zeit']) . '">';
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
        const selectPopup = document.getElementById('selectPopup');
        const tagSelect = document.getElementById('tagSelect');
        const openAufgabe = document.getElementById('openAufgabe');
        const popup1 = document.getElementById('popupForm1');
        const popup2 = document.getElementById('popupForm2');
        const btn = document.getElementById('neueBtn');
        const closes = document.querySelectorAll('.popup .close');

        btn.onclick = () => { selectPopup.style.display = 'flex'; }

        openAufgabe.onclick = () => {
            const tag = tagSelect.value || '';
            document.querySelectorAll('.popup-tag').forEach(el => el.value = tag);
            selectPopup.style.display = 'none';
            popup1.style.display = 'flex';
        }

        // Neuer Teil: Klick auf bestehende Stunde öffnet Schulaufgaben-Popup
        document.querySelectorAll('.stunde').forEach(el => {
            el.addEventListener('click', () => {
                const tag = el.getAttribute('data-tag') || '';
                const fach = el.getAttribute('data-fach') || '';
                const zeit = el.getAttribute('data-zeit') || '';
                // Fülle versteckte Felder im Schulaufgaben-Formular
                const aufgabeTag = document.querySelector('#popupForm2 input[name="tag"]');
                const aufgabeFach = document.querySelector('#popupForm2 input[name="fach"]');
                const aufgabeZeit = document.querySelector('#popupForm2 input[name="zeit"]');
                const fachVisible = document.querySelector('#popupForm2 input[name="fach_visible"]');
                if (aufgabeTag) aufgabeTag.value = tag;
                if (aufgabeFach) aufgabeFach.value = fach;
                if (aufgabeZeit) aufgabeZeit.value = zeit;
                if (fachVisible) { fachVisible.value = fach; fachVisible.readOnly = true; }
                popup2.style.display = 'flex';
            });
        });

        closes.forEach(c => c.addEventListener('click', e => {
            e.target.closest('.popup').style.display = 'none';
        }));

        window.addEventListener('click', e => {
            if (e.target.classList.contains('popup')) e.target.style.display = 'none';
        });
    </script>
</body>
</html>