

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abwesenheit</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="abwesenheit.css">
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

        
</body>
</html>
