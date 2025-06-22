<?php

require_once '../utils/connexio.php';
require_once '../utils/logger.php';

if (isset($_SESSION['usuari']['nom'])) {
    $usuari = $_SESSION['usuari']['nom'];
    $url = $_SERVER['REQUEST_URI'];
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconegut';

    registraAcces($usuari, $url, $navegador);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom = clear_input($_POST["nom"] ?? '');
    $cognom = clear_input($_POST["cognom"] ?? '');
    $email = clear_input($_POST["email"] ?? '');
    $departament_id = intval($_POST["departament"] ?? 0);
    $titol = clear_input($_POST["titol"] ?? '');
    $descripcio = clear_input($_POST["incidencia"] ?? '');

    if (!$nom || !$cognom || !$email || !$departament_id || !$titol || !$descripcio) {
        die("Tots els camps són obligatoris.");
    }

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connexió fallida: " . $conn->connect_error);
    }

    $nom_complet = $nom . " " . $cognom;

    $sql_check = "SELECT id_usuari FROM USUARI WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $row = $result_check->fetch_assoc();
        $usuari_id = $row['id_usuari'];
        $stmt_check->close();
    } else {
        $stmt_check->close();
        $sql_usuari = "INSERT INTO USUARI (nom, email, departament_id) VALUES (?, ?, ?)";
        $stmt_usuari = $conn->prepare($sql_usuari);
        if (!$stmt_usuari) {
            die("Error preparant USUARI: " . $conn->error);
        }
        $stmt_usuari->bind_param("ssi", $nom_complet, $email, $departament_id);
        if (!$stmt_usuari->execute()) {
            die("Error inserint usuari: " . $stmt_usuari->error);
        }
        $usuari_id = $stmt_usuari->insert_id;
        $stmt_usuari->close();
    }

    $sql_incidencia = "INSERT INTO INCIDENCIA (usuari_id, titol, descripcio) VALUES (?, ?, ?)";
    $stmt_inc = $conn->prepare($sql_incidencia);
    if (!$stmt_inc) {
        die("Error preparant INCIDENCIA: " . $conn->error);
    }
    $stmt_inc->bind_param("iss", $usuari_id, $titol, $descripcio);
    if (!$stmt_inc->execute()) {
        die("Error inserint incidència: " . $stmt_inc->error);
    }

    $stmt_inc->close();
    $conn->close();
    ?>

    <!DOCTYPE html>
    <html lang="ca">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Incidència registrada</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="estils.css">
        <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon" />
    </head>
    <body class="d-flex flex-column min-vh-100">
        <main class="container flex-grow-1 py-5 px-3">
            <div class="row g-4 align-items-start">
                <section class="col-12 col-md-6">
                    <h1 class="fw-bold">Gestió d'Incidències Informàtiques</h1>
                    <p>
                        Aquesta aplicació web té com a objectiu permetre el registre i la gestió d’incidències informàtiques
                        dins d’una organització.
                    </p>
                </section>

                <section class="col-12 col-md-6">
                    <div class="card shadow p-4">
                        <h1 class="mb-3">Incidència registrada correctament</h1>
                        <p>Gràcies, <strong><?php echo $nom_complet; ?></strong>. La teva incidència s'ha enregistrat correctament.</p>
                        <a class="btn btn-primary mt-3 w-100" href="./">Tornar</a>
                    </div>
                </section>
            </div>
        </main>
    </body>
    </html>

<?php } ?>
