<?php
session_start();
require_once '../utils/logger.php';

if (isset($_SESSION['usuari']['nom'])) {
    $usuari = $_SESSION['usuari']['nom'];
    $url = $_SERVER['REQUEST_URI'];
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconegut';

    registraAcces($usuari, $url, $navegador);
}
if (!isset($_SESSION['usuari']) || empty($_SESSION['usuari']['rol'])) {
    header("Location: ../utils/login.php");
    exit;
}

$rol = $_SESSION['usuari']['rol'];
$rolesPermitidos = ['admin', 'tecnic'];
if (!in_array($rol, $rolesPermitidos)) {
    header("HTTP/1.1 403 Forbidden");
    die("No tens permisos per accedir a aquesta pàgina.");
}

require_once '../utils/connexio.php';

function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$perPagina = 10;
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $perPagina;
$actualitzat = isset($_GET['actualitzat']);
$estatFiltrat = $_GET['estat'] ?? '';
$prioritatFiltrada = $_GET['prioritat'] ?? '';
$dataOrdre = isset($_GET['ordre_data']) && in_array($_GET['ordre_data'], ['asc', 'desc']) ? $_GET['ordre_data'] : 'desc';
$idBuscat = $_GET['id_incidencia'] ?? '';
$tecnicBuscat = $_GET['tecnic'] ?? '';

$condicions = [];
$params = [];
$types = "";

if ($rol === 'tecnic') {
    $condicions[] = "I.estat != 'Resolta'";
    $condicions[] = "I.eliminat = 0";
}

if ($estatFiltrat !== '') {
    $condicions[] = "I.estat = ?";
    $params[] = $estatFiltrat;
    $types .= "s";
}
if ($prioritatFiltrada !== '') {
    $condicions[] = "I.prioritat = ?";
    $params[] = $prioritatFiltrada;
    $types .= "s";
}
if ($idBuscat !== '') {
    $condicions[] = "I.id_incidencia = ?";
    $params[] = $idBuscat;
    $types .= "i";
}
if ($tecnicBuscat !== '') {
    $condicions[] = "T.nom LIKE ?";
    $params[] = "%$tecnicBuscat%";
    $types .= "s";
}

$condicionsStr = $condicions ? implode(" AND ", $condicions) : "1=1";

$countQuery = "
    SELECT COUNT(*) AS total 
    FROM INCIDENCIA I
    LEFT JOIN TECNIC T ON I.tecnic_id = T.id_tecnic
    WHERE $condicionsStr
";
$countStmt = $conn->prepare($countQuery);
if ($types) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalIncidencies = $countResult->fetch_assoc()['total'];
$totalPagines = ceil($totalIncidencies / $perPagina);
$countStmt->close();

$query = "
    SELECT I.id_incidencia, TI.nom AS tipus_incidencia, I.descripcio, I.estat, I.prioritat, 
           I.data_creacio, T.nom AS tecnic_nom, I.eliminat
    FROM INCIDENCIA I
    LEFT JOIN TECNIC T ON I.tecnic_id = T.id_tecnic
    LEFT JOIN TIPUS_INCIDENCIA TI ON I.tipus_id = TI.id_tipus
    WHERE $condicionsStr
    ORDER BY I.data_creacio " . strtoupper($dataOrdre) . "
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error en la consulta: " . $conn->error);
}

if ($types) {
    $typesFull = $types . "ii";
    $paramsFull = array_merge($params, [$perPagina, $offset]);
    $stmt->bind_param($typesFull, ...$paramsFull);
} else {
    $stmt->bind_param("ii", $perPagina, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<?php include '../utils/header.php'; ?>

<h2 class="mb-4 text-center">Llistat d'Incidències</h2>

<form method="GET" class="d-flex flex-wrap justify-content-center gap-3 mb-4">
    <div class="col-auto">
        <input type="number" name="id_incidencia" class="form-control" placeholder="ID Incidència" style="max-width: 120px;" value="<?= htmlspecialchars($idBuscat) ?>">
    </div>
    <div class="col-auto">
        <select name="estat" class="form-select">
            <option value="">Tots els estats</option>
            <option value="Pendent" <?= $estatFiltrat === 'Pendent' ? 'selected' : '' ?>>Pendent</option>
            <option value="En procés" <?= $estatFiltrat === 'En procés' ? 'selected' : '' ?>>En procés</option>
            <option value="Resolta" <?= $estatFiltrat === 'Resolta' ? 'selected' : '' ?>>Resolta</option>
        </select>
    </div>
    <div class="col-auto">
        <select name="prioritat" class="form-select">
            <option value="">Totes les prioritats</option>
            <option value="Alta" <?= $prioritatFiltrada === 'Alta' ? 'selected' : '' ?>>Alta</option>
            <option value="Mitja" <?= $prioritatFiltrada === 'Mitja' ? 'selected' : '' ?>>Mitja</option>
            <option value="Baixa" <?= $prioritatFiltrada === 'Baixa' ? 'selected' : '' ?>>Baixa</option>
        </select>
    </div>
    <div class="col-auto">
        <select name="ordre_data" class="form-select">
            <option value="asc" <?= $dataOrdre === 'asc' ? 'selected' : '' ?>>Més antigues</option>
            <option value="desc" <?= $dataOrdre === 'desc' ? 'selected' : '' ?>>Més noves</option>
        </select>
    </div>
    <div class="col-auto">
        <input type="text" class="form-control" name="tecnic" placeholder="Nom tècnic" value="<?= htmlspecialchars($tecnicBuscat) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="?" class="btn btn-secondary">Reset</a>
    </div>
</form>

<div class="table-responsive mx-auto" style="width: 90%;">
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-primary text-center">
            <tr>
                <th>ID</th>
                <th>Tipus</th>
                <th>Descripció</th>
                <th>Estat</th>
                <th>Prioritat</th>
                <th>Data</th>
                <th>Assignat</th>
                <th>Accions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                        $classeFila = '';
                        if ($rol === 'admin') {
                            if ((int)$row['eliminat'] === 1) {
                                $classeFila = 'table-danger';
                            } elseif (strtolower($row['estat']) === 'resolta') {
                                $classeFila = 'table-success';
                            }
                        }
                    ?>
                    <tr class="<?= $classeFila ?>">
                        <td><?= htmlspecialchars($row['id_incidencia']) ?></td>
                        <td><?= htmlspecialchars($row['tipus_incidencia']) ?></td>
                        <td><?= htmlspecialchars($row['descripcio'] ?? '') ?></td>
                        <td>
                            <?php
                                $estat = strtolower(trim($row['estat'] ?? ''));
                                $estatBadge = match ($estat) {
                                    'pendent' => 'bg-secondary',
                                    'en procés', 'en proces' => 'bg-warning text-dark',
                                    'resolta' => 'bg-success',
                                    default => 'bg-secondary',
                                };
                            ?>
                            <span class="badge <?= $estatBadge ?>"><?= htmlspecialchars($row['estat']) ?></span>
                        </td>
                        <td>
                            <?php
                                $prioritat = strtolower(trim($row['prioritat'] ?? ''));
                                $prioritatText = ucfirst($prioritat ?: 'No definida');
                                $prioritatClass = match ($prioritat) {
                                    'alta' => 'badge-alta',
                                    'mitja' => 'badge-mitja',
                                    'baixa' => 'badge-baixa',
                                    default => 'badge-null',
                                };
                            ?>
                            <span class="badge <?= $prioritatClass ?>"><?= $prioritatText ?></span>
                        </td>
                        <td><?= htmlspecialchars($row['data_creacio']) ?></td>
                        <td><?= htmlspecialchars($row['tecnic_nom'] ?? 'No assignat') ?></td>
                        <td class="text-center">
                            <a href="editar_incidencia.php?id=<?= $row['id_incidencia'] ?>" class="btn btn-sm btn-info">Editar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center">No hi ha incidències registrades.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<nav aria-label="Paginació d'incidències">
    <ul class="pagination justify-content-center">
        <?php if ($paginaActual > 1): ?>
            <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $paginaActual - 1])) ?>">Anterior</a></li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPagines; $i++): ?>
            <li class="page-item <?= ($i === $paginaActual) ? 'active' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($paginaActual < $totalPagines): ?>
            <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $paginaActual + 1])) ?>">Següent</a></li>
        <?php endif; ?>
    </ul>
</nav>

<?php if ($actualitzat): ?>
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
  <div id="toastActualitzat" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">✅ Canvis desats correctament</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tancar"></button>
    </div>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const toastEl = document.getElementById("toastActualitzat");
    const toast = new bootstrap.Toast(toastEl, { delay: 4000, autohide: true });
    toast.show();
  });
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php
$stmt->close();
$conn->close();
include '../utils/footer.php';
?>
