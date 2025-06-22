<?php
require_once '../utils/connexio.php';
require_once '../utils/logger.php';

if (isset($_SESSION['usuari']['nom'])) {
    $usuari = $_SESSION['usuari']['nom'];
    $url = $_SERVER['REQUEST_URI'];
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconegut';

    registraAcces($usuari, $url, $navegador);
}

function clear_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $nom = clear_input($_POST["nom"] ?? '');
        $cognom = clear_input($_POST["cognom"] ?? '');
        $email = clear_input($_POST["email"] ?? '');
        $departament_id = intval($_POST["departament"] ?? 0);
        $tipus_id = intval($_POST["id_tipus"] ?? 0);  
        $descripcio = clear_input($_POST["incidencia"] ?? '');

        if (!$nom || !$email || !$departament_id || !$tipus_id || !$descripcio) {
            throw new Exception("Tots els camps són obligatoris.");
        }

        $conn = new mysqli($servername, $username, $password, $database);
        $conn->set_charset("utf8mb4");

        $nom_complet = $nom . ($cognom ? " $cognom" : "");

        $sql_check = "SELECT id_usuari FROM USUARI WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $row = $result_check->fetch_assoc();
            $usuari_id = $row['id_usuari'];
        } else {
            $sql_usuari = "INSERT INTO USUARI (nom, email, departament_id) VALUES (?, ?, ?)";
            $stmt_usuari = $conn->prepare($sql_usuari);
            $stmt_usuari->bind_param("ssi", $nom_complet, $email, $departament_id);
            $stmt_usuari->execute();
            $usuari_id = $stmt_usuari->insert_id;
            $stmt_usuari->close();
        }
        $stmt_check->close();

        $sql_incidencia = "INSERT INTO INCIDENCIA (usuari_id, tipus_id, descripcio) VALUES (?, ?, ?)";
        $stmt_inc = $conn->prepare($sql_incidencia);
        $stmt_inc->bind_param("iis", $usuari_id, $tipus_id, $descripcio);
        $stmt_inc->execute();
        $incidencia_id = $stmt_inc->insert_id;
        $stmt_inc->close();

        $conn->close();

    } catch (Exception $e) {
        die("S'ha produït un error: " . $e->getMessage());
    }
} else {
    header("Location: form.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Incidència registrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon" />
    <style>
        body {
            background-color: #f5f7fa;
            color: #333;
        }
        .confirmation-box {
            max-width: 700px;
            margin: 4rem auto;
            padding: 2.5rem;
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            text-align: center;
        }
        .confirmation-box h1 {
            font-size: 2rem;
            font-weight: 600;
            color: #0d6efd;
        }
        .confirmation-box p {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        .btn-primary {
            border-radius: 2rem;
            padding: 0.6rem 1.5rem;
            font-size: 1rem;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>

<div class="confirmation-box">
    <h1>Incidència registrada amb èxit</h1>
    <p>Gràcies, <strong><?= htmlspecialchars($nom_complet) ?></strong>. La teva incidència ha estat enregistrada correctament.</p>
    <p><strong>Codi d'incidència:</strong> <?= $incidencia_id ?></p>
    <a href="../utils/dashboard.php" class="btn btn-primary mt-3">Tornar a l'inici</a>
</div>

</body>
</html>
