

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code-Verifizierung</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Bestätigungscode eingeben</h1>
        <p>Wir haben dir einen Code an deine E-Mail gesendet.</p>

        <form method="POST" action="">
            <input type="text" name="code" placeholder="6-stelliger Code" required><br><br>
            <button type="submit" class="btn">Bestätigen</button>
        </form>

        <?php
        if (!empty($error)) echo "<p style='color:red;'>$error</p>";
        if (!empty($success)) echo "<p style='color:green;'>$success</p>";
        ?>
    </div>
</body>
</html>
