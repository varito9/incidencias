<?php
require_once 'connexio.php'; 
require_once 'auth.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $contrasenya = $_POST['contrasenya'] ?? '';

    if (!$email) {
        $error = 'Correu electrònic invàlid.';
    } elseif (!$contrasenya) {
        $error = 'Contrasenya buida.';
    } else {
        $stmt = $conn->prepare("SELECT id_tecnic, nom, email, administrador, contrasenya FROM TECNIC WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($usuari = $result->fetch_assoc()) {
                if (password_verify($contrasenya, $usuari['contrasenya'])) {
                    session_regenerate_id(true);
                    $_SESSION['usuari'] = [
                        'id' => $usuari['id_tecnic'],
                        'nom' => $usuari['nom'],
                        'email' => $usuari['email'],
                        'rol' => ($usuari['administrador'] == 1) ? 'admin' : 'tecnic'
                    ];
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = 'Contrasenya incorrecta.';
                }
            }
            $stmt->close();
        }

        if (!$usuari) {
            $stmt = $conn->prepare("SELECT id_usuari, nom, email, contrasenya FROM USUARI WHERE email = ?");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($usuari = $result->fetch_assoc()) {
                    if (password_verify($contrasenya, $usuari['contrasenya'])) {
                        session_regenerate_id(true);
                        $_SESSION['usuari'] = [
                            'id' => $usuari['id_usuari'],
                            'nom' => $usuari['nom'],
                            'email' => $usuari['email'],
                            'rol' => 'usuari'
                        ];
                        header("Location: dashboard.php");
                        exit;
                    } else {
                        $error = 'Contrasenya incorrecta.';
                    }
                } else {
                    $error = 'Usuari no trobat.';
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Inici de Sessió</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon" />
</head>
<body class="bg-light">

    <div class="container-fluid min-vh-100 d-flex justify-content-center align-items-center px-3">
        <div class="card p-4 shadow w-100" style="max-width: 400px;">
            <h3 class="text-center mb-3">Inici de Sessió</h3>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Correu electrònic</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        required
                        autofocus
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                    />
                </div>
                <div class="mb-3">
                    <label for="contrasenya" class="form-label">Contrasenya</label>
                    <input
                        type="password"
                        id="contrasenya"
                        name="contrasenya"
                        class="form-control"
                        required
                    />
                </div>
                <p class="text-center mt-3">
                    <a href="singup.php">No tens compte? Registra't</a>
                </p>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>

</body>
</html>

