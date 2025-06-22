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

$id_incidencia = $_POST['id_incidencia'] ?? ($_GET['id'] ?? null);
if (!$id_incidencia) {
    die("ID de la incidència no proporcionat.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resoldre'])) {
    $sql_update = "UPDATE INCIDENCIA SET estat = 'Resolta', resolta = 1 WHERE id_incidencia = ?";
    $stmt_update = $conn->prepare($sql_update);
    
    if (!$stmt_update) {
        die("Error al preparar la consulta: " . $conn->error);
    }

    $stmt_update->bind_param("i", $id_incidencia);
    $stmt_update->execute();
    $stmt_update->close();
    
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id_incidencia);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descripcio'])) {
    $descripcio = $_POST['descripcio'] ?? '';
    $visible_usuari = isset($_POST['visible_usuari']) ? 1 : 0;
    $tecnic_id = $_SESSION['usuari']['id'] ?? null;

    if (!$tecnic_id) {
        die("Error: Tècnic no identificat a la sessió.");
    }

    if (!empty($descripcio)) {
        $sql = "INSERT INTO ACTUACIO (descripcio, data_creacio, visible_usuari, tecnic_id, incidencia_id) 
                VALUES (?, NOW(), ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error al preparar la consulta: " . $conn->error);
        }

        $stmt->bind_param("siii", $descripcio, $visible_usuari, $tecnic_id, $id_incidencia);
        $stmt->execute();
        $stmt->close();
    }
}

$docs = [];
$result = $conn->prepare("SELECT * FROM ACTUACIO WHERE incidencia_id = ? ORDER BY data_creacio DESC");
$result->bind_param("i", $id_incidencia);
$result->execute();
$docs_result = $result->get_result();
while ($row = $docs_result->fetch_assoc()) {
    $docs[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Documentació</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estils.css?v=<?= time() ?>">
    <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon" />
    <style>
        .timeline-item {
            margin-bottom: 1.5rem;
        }

        .timeline-content p {
            word-wrap: break-word;
        }

        @media (max-width: 576px) {
            h2.display-6 {
                font-size: 1.5rem;
                text-align: center;
            }
            form.mb-5, .timeline {
                padding: 0 0.5rem;
            }
            .btn, textarea.form-control {
                width: 100% !important;
            }
            .d-flex.align-items-start {
                flex-direction: column !important;
            }
            .btn-outline-danger.btn-sm {
                margin-bottom: 0.5rem;
                width: 3rem;
                align-self: flex-start;
            }
            .timeline-content {
                flex-grow: 1;
            }
            .modal-dialog {
                max-width: 95%;
                margin: 1rem auto;
            }
            .d-flex.flex-column.flex-md-row.gap-20 {
                gap: 1rem !important;
            }
        }
    </style>
</head>
<body>
<div class="container py-5 animacio-entrada">
    <div class="card-custom mx-auto" style="max-width: 900px;">
        <h2 class="mb-4 display-6 fw-bold"><i class="bi bi-file-earmark-text-fill me-2"></i>Documentació</h2>

        <form method="POST" class="mb-5">
            <div class="mb-3">
                <label for="descripcio" class="form-label">Acció realitzada</label>
                <textarea name="descripcio" id="descripcio" class="form-control p-3 rounded-4 shadow-sm" rows="4" required></textarea>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="visible_usuari" name="visible_usuari">
                <label class="form-check-label" for="visible_usuari">
                    Visible per a l'usuari
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-lg transition d-flex align-items-center gap-2">
                <i class="bi bi-chat-left-text-fill"></i> Afegir documentació
            </button>
        </form>

        <h4 class="mb-4"><i class="bi bi-journal-text me-2"></i>Historial</h4>
        <?php if (empty($docs)): ?>
            <p class="text-muted">No hi ha documentació registrada.</p>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($docs as $doc): ?>
                    <div class="timeline-item">
                        <div class="d-flex align-items-start">
                            <button type="button" class="btn btn-outline-danger btn-sm me-3" data-bs-toggle="modal" data-bs-target="#confirmModal<?php echo $doc['id_actuacio']; ?>">
                                <i class="bi bi-dash-circle"></i>
                            </button>

                            <div class="timeline-content">
                                <p class="mb-1 fw-bold text-primary"><?php echo $doc['data_creacio']; ?></p>
                                <p><?php echo nl2br(htmlspecialchars($doc['descripcio'])); ?>
                                    <?php if ($doc['visible_usuari']): ?>
                                        <span class="badge bg-success ms-2">Visible</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="confirmModal<?php echo $doc['id_actuacio']; ?>" tabindex="-1" aria-labelledby="confirmLabel<?php echo $doc['id_actuacio']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="POST" action="eliminar_actuacio.php">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title" id="confirmLabel<?php echo $doc['id_actuacio']; ?>">Confirmar eliminació</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tancar"></button>
                                    </div>
                                    <div class="modal-body">
                                        Segur que vols eliminar aquesta actuació? Aquesta acció no es pot desfer.
                                    </div>
                                    <div class="modal-footer">
                                        <input type="hidden" name="id_actuacio" value="<?php echo $doc['id_actuacio']; ?>">
                                        <input type="hidden" name="id_incidencia" value="<?php echo $id_incidencia; ?>">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($_SESSION['usuari']['rol'] === 'admin'): ?>
            <form method="POST" class="mt-4">
                <input type="hidden" name="id_incidencia" value="<?= htmlspecialchars($id_incidencia) ?>">
                <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#confirmMarkResolvedModal">
                    ✅ Marcar com a resolta
                </button>
            </form>

            <div class="modal fade" id="confirmMarkResolvedModal" tabindex="-1" aria-labelledby="confirmMarkResolvedModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title" id="confirmMarkResolvedModalLabel">Confirmar resolució</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tancar"></button>
                            </div>
                            <div class="modal-body">
                                Estàs segur que vols marcar aquesta incidència com a resolta? Aquesta acció només la pot desfer un administrador.
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" name="id_incidencia" value="<?= htmlspecialchars($id_incidencia) ?>">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                                <button type="submit" name="resoldre" class="btn btn-success">Marcar com a resolta</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-4">
            <div class="d-flex flex-column flex-md-row gap-20 w-100 w-md-auto">
                <a href="editar_incidencia.php?id=<?php echo $id_incidencia; ?>" class="btn btn-outline-secondary w-100">
                    ↩️ Tornar
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../utils/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
