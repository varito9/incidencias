<?php
session_start();
require_once '../utils/connexio.php';
require_once '../utils/logger.php';

if (isset($_SESSION['usuari']['nom'])) {
    $usuari = $_SESSION['usuari']['nom'];
    $url = $_SERVER['REQUEST_URI'];
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconegut';

    registraAcces($usuari, $url, $navegador);
}
if (!isset($_SESSION['usuari'])) {
    header("Location: ../utils/login.php");
    exit;
}

$usuari = $_SESSION['usuari'];
$nom = htmlspecialchars($usuari['nom'] ?? '');
$cognom = htmlspecialchars($usuari['cognom'] ?? '');
$email = htmlspecialchars($usuari['email'] ?? '');
$departament_id = intval($usuari['departament_id'] ?? 0);

$departaments = [];
$sqlDepartaments = "SELECT id_departament, nom FROM DEPARTAMENT";
$resultDepartaments = $conn->query($sqlDepartaments);
if ($resultDepartaments && $resultDepartaments->num_rows > 0) {
    while ($row = $resultDepartaments->fetch_assoc()) {
        $departaments[] = $row;
    }
}

$tipusIncidencia = [];
$sqlIncidencia = "SELECT id_tipus, nom FROM TIPUS_INCIDENCIA";
$resultIncidencia = $conn->query($sqlIncidencia);
if ($resultIncidencia && $resultIncidencia->num_rows > 0) {
    while ($row = $resultIncidencia->fetch_assoc()) {
        $tipusIncidencia[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Crear Incidència</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon" />
    <?php include '../utils/header.php'; ?>

    <style>
        body {
            background-color: #f5f7fa;
            color: #333;
        }
        .container-form {
            max-width: 850px;
            margin: 3rem auto;
            background: #fff;
            padding: 2.5rem;
            border-radius: 1.25rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        }
        h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        .form-control,
        .form-select,
        textarea {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: border 0.3s;
        }
        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13,110,253,.15);
        }
        .btn {
            padding: 0.6rem 1.5rem;
            font-size: 1rem;
        }
        .btn-outline-primary:hover {
            background-color: #0d6efd;
            color: #fff;
        }
        .text-muted {
            font-size: 0.95rem;
        }
        .row > .col {
            margin-bottom: 1rem;
        }
        @media (max-width: 768px) {
            .container-form {
                margin: 1.5rem;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="container container-form animacio-entrada">
    <h1>Crear una nova incidència</h1>
    <p class="text-muted mb-4">
        Omple el formulari següent per registrar una incidència. Aquesta es gestionarà pel departament tècnic corresponent.
    </p>

    <form id="formulariIncidencia" method="POST" action="crear_incidencia.php" novalidate>
        <input type="hidden" name="nom" value="<?= $nom ?>">
        <input type="hidden" name="cognom" value="<?= $cognom ?>">

        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

        <div class="mb-3">
    <label for="departament" class="form-label">Departament</label>
    <select id="departament" name="departament" class="form-select" required>
        <option value="" disabled selected>Selecciona el teu Departament</option>
        <?php foreach ($departaments as $d): ?>
            <option value="<?= $d['id_departament'] ?>" <?= $d['id_departament'] == $departament_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($d['nom']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

        <div class="mb-3">
            <label for="id_tipus" class="form-label">Categoria de la incidència</label>
            <select id="id_tipus" name="id_tipus" class="form-select" required>
                <option value="" disabled selected hidden>Selecciona una categoria</option>
                <?php foreach ($tipusIncidencia as $incidencia): ?>
                    <option value="<?= $incidencia['id_tipus'] ?>"><?= htmlspecialchars($incidencia['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="incidencia" class="form-label">Descripció</label>
            <textarea id="incidencia" name="incidencia" class="form-control" rows="4" placeholder="Descriu el problema detectat..." required></textarea>
        </div>

        <div id="errorMissatge" class="text-danger mb-3 d-none">Tots els camps són obligatoris.</div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-outline-primary w-100">Enviar</button>
            </div>
        </div>
    </form>
</div>
<?php include '../utils/footer.php'; ?>

<script src="js/validacio.js"></script>
</body>
</html>
