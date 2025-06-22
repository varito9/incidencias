<?php
require_once 'connexio.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contrasenya = $_POST['contrasenya'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$nom || !$email || !$contrasenya || !$confirm) {
        $error = "Tots els camps són obligatoris.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Correu electrònic invàlid.";
    } elseif ($contrasenya !== $confirm) {
        $error = "Les contrasenyes no coincideixen.";
    } else {
    $checkStmt = $conn->prepare("SELECT id_usuari FROM USUARI WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $error = "Aquest correu ja està registrat.";
    } else {
        $hash = password_hash($contrasenya, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO USUARI (nom, email, contrasenya) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nom, $email, $hash);

        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $_SESSION['usuari_id'] = $id;
            $_SESSION['usuari_nom'] = $nom;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Error al registrar l'usuari.";
        }

        $stmt->close();
    }

    $checkStmt->close();
}
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar-se</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon" />

</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card p-4 shadow w-100" style="max-width: 400px;">
        <h3 class="text-center mb-3">Crear compte</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Correu electrònic</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contrasenya</label>
                <input type="password" name="contrasenya" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirma la contrasenya</label>
                <input type="password" name="confirm" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Registrar-me</button>
            <p class="text-center mt-3">
                <a href="login.php">Ja tens compte? Inicia sessió</a>
            </p>
        </form>
    </div>
</div>

</body>
</html>
