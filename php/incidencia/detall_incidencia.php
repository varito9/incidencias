<?php
session_start();
require_once '../utils/logger.php';

if (isset($_SESSION['usuari']['nom'])) {
    $usuari = $_SESSION['usuari']['nom'];
    $url = $_SERVER['REQUEST_URI'];
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconegut';

    registraAcces($usuari, $url, $navegador);
}

if (!isset($_SESSION['usuari'])) {
    header('Location: ../utils/login.php');
    exit();
}

require_once '../utils/connexio.php';
require_once '../utils/utils.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function clear_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$incidencia = null;
$accions = [];

if (isset($_GET["id"])) {
    $id_incidencia = clear_input($_GET["id"]);

    try {
        $conn = new mysqli($servername, $username, $password, $database);
        $conn->set_charset("utf8mb4");

        $sql = "SELECT i.id_incidencia, i.descripcio, i.data_creacio, i.tipus_id, i.estat,
                       t.nom AS tipus_nom, u.nom AS usuari_nom, u.email
                FROM INCIDENCIA i
                JOIN USUARI u ON i.usuari_id = u.id_usuari
                JOIN TIPUS_INCIDENCIA t ON i.tipus_id = t.id_tipus
                WHERE i.id_incidencia = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_incidencia);
        $stmt->execute();
        $result = $stmt->get_result();
        $incidencia = $result->fetch_assoc();
        $stmt->close();

        $sql_accions = "SELECT a.descripcio, a.data_creacio, t.nom AS tecnic_nom
                        FROM ACTUACIO a
                        JOIN TECNIC t ON a.tecnic_id = t.id_tecnic
                        WHERE a.incidencia_id = ? AND a.visible_usuari = 1
                        ORDER BY a.data_creacio ASC";
        $stmt_accions = $conn->prepare($sql_accions);
        $stmt_accions->bind_param("i", $id_incidencia);
        $stmt_accions->execute();
        $result_accions = $stmt_accions->get_result();
        while ($row = $result_accions->fetch_assoc()) {
            $accions[] = $row;
        }
        $stmt_accions->close();

        $conn->close();

    } catch (Exception $e) {
        die("S'ha produït un error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Detall Incidència</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon" />
    <style>
        body {
            background-color: #f8f9fc;
        }

        .container {
            margin-top: 40px;
            padding: 0 15px;
        }

        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            word-wrap: break-word;
        }

        .card-header {
            background-color: #4e73df;
            color: #fff;
            font-weight: 500;
            font-size: 1.25rem;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }

        .badge-pendent {
            background-color: #f0ad4e;
        }

        .badge-proces {
            background-color: #5bc0de;
        }

        .badge-resolta {
            background-color: #5cb85c;
        }

        @media (max-width: 576px) {
            h1 {
                font-size: 1.5rem;
                text-align: center;
            }

            .card-header {
                font-size: 1rem;
                text-align: center;
            }

            .btn {
                width: 100%;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center mb-4">Detall de la Incidència</h1>

    <?php if ($incidencia): ?>
        <div class="card mb-4">
            <div class="card-header">
                Incidència #<?= htmlspecialchars($incidencia['id_incidencia']); ?>
            </div>
            <div class="card-body">
                <p><strong>Estat:</strong>
                    <?php
                        $estat = $incidencia['estat'];
                        $badge_class = match ($estat) {
                            'pendent' => 'badge-pendent',
                            'en procés' => 'badge-proces',
                            'resolta' => 'badge-resolta',
                            default => 'bg-secondary',
                        };
                    ?>
                    <span class="badge <?= $badge_class; ?>">
                        <?= ucfirst($estat); ?>
                    </span>
                </p>
                <p><strong>Tipus:</strong> <?= htmlspecialchars($incidencia['tipus_nom']); ?></p>
                <p class="text-break"><strong>Descripció:</strong> <?= htmlspecialchars($incidencia['descripcio']); ?></p>
                <p><strong>Data creació:</strong> <?= htmlspecialchars($incidencia['data_creacio']); ?></p>
                <p><strong>Usuari:</strong> <?= htmlspecialchars($incidencia['usuari_nom']); ?> (<?= htmlspecialchars($incidencia['email']); ?>)</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Comentaris del Tècnic</div>
            <div class="card-body">
                <?php if (count($accions) > 0): ?>
                    <div class="table-responsive">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($accions as $accio): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($accio['tecnic_nom']); ?>:</strong>
                                    <?= htmlspecialchars($accio['descripcio']); ?>
                                    <span class="text-muted float-end"><?= htmlspecialchars($accio['data_creacio']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="mb-0">No hi ha comentaris visibles del tècnic per aquesta incidència.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger mt-4">No s'ha trobat cap incidència amb l'identificador proporcionat.</div>
    <?php endif; ?>

    <div class="mt-4 text-center text-md-start">
        <a href="consulta_incidencia.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Enrere
        </a>
    </div>
</div>

<?php include '../utils/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
