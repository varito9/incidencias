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

$id_incidencia = $_GET['id'] ?? null;
if ($id_incidencia === null || !is_numeric($id_incidencia)) {
    die("ID de incidència no proporcionat o invàlid.");
}

$sql = "SELECT * FROM INCIDENCIA WHERE id_incidencia = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en la preparació de la consulta SQL: " . $conn->error);
}
$stmt->bind_param("i", $id_incidencia);
$stmt->execute();
$result = $stmt->get_result();
$incidencia = $result->fetch_assoc();

if (!$incidencia) {
    die("Incidència no trobada.");
}

$tecnics = [];
$tecnic_query = $conn->query("SELECT id_tecnic, nom FROM TECNIC");
while ($row = $tecnic_query->fetch_assoc()) {
    $tecnics[] = $row;
}

$tipus_incidencies = [];
$tipus_query = $conn->query("SELECT id_tipus, nom FROM TIPUS_INCIDENCIA");
while ($row = $tipus_query->fetch_assoc()) {
    $tipus_incidencies[] = $row;
}

$prioritat = $incidencia['prioritat'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipus = $_POST['tipus'] ?? '';
    $estat_nou = $_POST['estat'] ?? $incidencia['estat'];
    $descripcio = trim($_POST['descripcio'] ?? '');
    $prioritat = $_POST['prioritat'] ?? null;
    $prioritat = ($prioritat === 'null') ? null : $prioritat;
    $tecnic_id = $_POST['tecnic_id'] ?? null;

    $estats_valids = ['Pendent', 'En procés', 'Resolta'];
    $prioritats_valides = ['Alta', 'Mitja', 'Baixa', null];

    if (!in_array($estat_nou, $estats_valids)) {
        die("Estat invàlid.");
    }

    if (!$tipus || !$descripcio) {
        die("Tipus i descripció no poden estar buits.");
    }

    if (!in_array($prioritat, $prioritats_valides, true)) {
        die("Prioritat invàlida.");
    }

    $tecnic_id = is_numeric($tecnic_id) ? (int)$tecnic_id : null;

    $sql = "UPDATE INCIDENCIA SET tipus_id = ?, estat = ?, descripcio = ?, prioritat = ?, tecnic_id = ? WHERE id_incidencia = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en la preparació de la consulta SQL: " . $conn->error);
    }
    $stmt->bind_param("ssssii", $tipus, $estat_nou, $descripcio, $prioritat, $tecnic_id, $id_incidencia);

    if ($stmt->execute()) {
        header("Location: incidencia.php?id=$id_incidencia&actualitzat=1");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error en l'actualització.</div>";
    }
}

$stmt->close();
$conn->close();

$actualitzat = isset($_GET['actualitzat']) && $_GET['actualitzat'] == 1;
?>
<?php include '../utils/header.php'; ?>


<?php if ($actualitzat): ?>
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
  <div id="toastGuardat" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">✅ Canvis desats correctament</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tancar"></button>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const toastElement = document.getElementById("toastGuardat");
    const toast = new bootstrap.Toast(toastElement, { delay: 4000, autohide: true });
    toast.show();

    const url = new URL(window.location);
    url.searchParams.delete('actualitzat');
    window.history.replaceState({}, document.title, url);
  });
</script>
<?php endif; ?>

<div class="container my-4 d-flex-center animacio-entrada">
    <div class="card shadow p-4">
        <h2 class="mb-4">Editar Incidència</h2>
        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="tipus" class="form-label">Tipus d'incidència</label>
                <select class="form-select" id="tipus" name="tipus" required>
                    <?php foreach ($tipus_incidencies as $tipus_item): ?>
                        <option value="<?= $tipus_item['id_tipus'] ?>" <?= $incidencia['tipus_id'] == $tipus_item['id_tipus'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipus_item['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="mb-3 col-12 col-md-4">
                    <label for="estat" class="form-label">Estat</label>
                    <select class="form-select" id="estat" name="estat" required>
                        <?php
                        
        $estat_actual = $incidencia['estat'];
        foreach (['Pendent', 'En procés', 'Resolta'] as $opcio):
        ?>
            <option value="<?= $opcio ?>" <?= $estat_actual === $opcio ? 'selected' : '' ?>><?= $opcio ?></option>
        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3 col-12 col-md-4">
                    <label for="prioritat" class="form-label">Prioritat</label>
                    <select name="prioritat" id="prioritat" class="form-select">
                        <?php if (is_null($prioritat) || $prioritat === ''): ?>
                            <option value="null" selected>-- No definida --</option>
                        <?php endif; ?>
                        <option value="Alta" <?= $prioritat === 'Alta' ? 'selected' : '' ?>>Alta</option>
                        <option value="Mitja" <?= $prioritat === 'Mitja' ? 'selected' : '' ?>>Mitja</option>
                        <option value="Baixa" <?= $prioritat === 'Baixa' ? 'selected' : '' ?>>Baixa</option>
                    </select>
                </div>

                <div class="mb-3 col-12 col-md-4">
                    <label for="tecnic_id" class="form-label">Assignar a tècnic</label>
                    <select class="form-select" id="tecnic_id" name="tecnic_id">
                        <?php if (empty($incidencia['tecnic_id'])): ?>
                            <option value="" selected>-- Sense tècnic --</option>
                        <?php endif; ?>
                        <?php foreach ($tecnics as $tecnic): ?>
                            <option value="<?= $tecnic['id_tecnic'] ?>" <?= $incidencia['tecnic_id'] == $tecnic['id_tecnic'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tecnic['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="descripcio" class="form-label">Descripció</label>
                <textarea class="form-control" id="descripcio" name="descripcio" rows="" readonly><?= htmlspecialchars($incidencia['descripcio']) ?></textarea>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-4">
                <button type="submit" class="btn btn-primary w-100 w-md-auto">💾 Desar canvis</button>
                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto">
                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#confirmModal">🗑️ Eliminar</button>
                    <a href="documentar_incidencia.php?id=<?= $id_incidencia ?>" class="btn btn-outline-info w-100">📄 Documentació</a>
                    <a href="incidencia.php" class="btn btn-outline-secondary w-100">↩️ Tornar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="eliminar_incidencia.php">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmLabel">Confirmar eliminació</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tancar"></button>
                </div>
                <div class="modal-body">
                    Segur que vols eliminar aquesta incidència? Aquesta acció no es pot desfer.
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id_incidencia" value="<?= $id_incidencia; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel·lar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../utils/footer.php'; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
