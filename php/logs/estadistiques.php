<?php
session_start();
require_once '../utils/connexio.php';
require_once '../utils/auth.php';
require '../vendor/autoload.php';
require_once '../utils/logger.php';

if (isset($_SESSION['usuari']['nom'])) {
    $usuari = $_SESSION['usuari']['nom'];
    $url = $_SERVER['REQUEST_URI'];
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconegut';

    registraAcces($usuari, $url, $navegador);
}

use MongoDB\Client;

if (!isset($_SESSION['usuari']['rol']) || $_SESSION['usuari']['rol'] !== 'admin'){
    header("Location: index.php?error=permís");
    exit;
}

$client = new Client("mongodb+srv://a24alvsalalv:nkJAdHbuQRhz90Zc@cluster0.9pxismp.mongodb.net/");
$collection = $client->RegistreLogs->ProjecteIncidencia;

$filtres = [];

if (!empty($_GET['usuari'])) {
    $filtres['usuari'] = $_GET['usuari'];
}
if (!empty($_GET['pagina'])) {
    $filtres['url'] = $_GET['pagina'];
}
if (!empty($_GET['data_inici']) && strtotime($_GET['data_inici']) !== false &&
    !empty($_GET['data_fi']) && strtotime($_GET['data_fi']) !== false) {

    $filtres['timestamp'] = [
        '$gte' => new MongoDB\BSON\UTCDateTime(strtotime($_GET['data_inici'] . ' 00:00:00') * 1000),
        '$lte' => new MongoDB\BSON\UTCDateTime(strtotime($_GET['data_fi'] . ' 23:59:59') * 1000),
    ];
}

$logs = $collection->find($filtres, [
    'sort' => ['timestamp' => -1],
    'limit' => 100
]);

$totalAccessos = $collection->countDocuments($filtres);

$pipelineUsuaris = [];
if (!empty($filtres)) {
    $pipelineUsuaris[] = ['$match' => $filtres];
}
$pipelineUsuaris = array_merge($pipelineUsuaris, [
    ['$group' => ['_id' => '$usuari', 'total' => ['$sum' => 1]]],
    ['$sort' => ['total' => -1]],
    ['$limit' => 5]
]);
$usuarisMesActius = $collection->aggregate($pipelineUsuaris);

$pipelinePagines = [];
if (!empty($filtres)) {
    $pipelinePagines[] = ['$match' => $filtres];
}
$pipelinePagines = array_merge($pipelinePagines, [
    ['$group' => ['_id' => '$url', 'total' => ['$sum' => 1]]],
    ['$sort' => ['total' => -1]],
    ['$limit' => 5]
]);
$paginesMesVisitades = $collection->aggregate($pipelinePagines);

$pipelineAccesosPerDia = [];
if (!empty($filtres)) {
    $pipelineAccesosPerDia[] = ['$match' => $filtres];
}
$pipelineAccesosPerDia = array_merge($pipelineAccesosPerDia, [
    ['$group' => [
        '_id' => ['$dateToString' => ['format' => "%Y-%m-%d", 'date' => '$timestamp']],
        'total' => ['$sum' => 1]
    ]],
    ['$sort' => ['_id' => 1]]
]);
$accesosPerDia = $collection->aggregate($pipelineAccesosPerDia);

?>
<?php include '../utils/header.php'; ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Estadístiques d'Accés</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon" />
</head>
<body class="bg-light">
<div class="container my-5">

    <h1 class="text-center mb-4">Estadístiques d'Accés</h1>

    <form method="get" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="usuari" class="form-control" placeholder="Usuari" value="<?= htmlspecialchars($_GET['usuari'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="pagina" class="form-control" placeholder="URL / Pàgina" value="<?= htmlspecialchars($_GET['pagina'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="data_inici" class="form-control" value="<?= htmlspecialchars($_GET['data_inici'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="data_fi" class="form-control" value="<?= htmlspecialchars($_GET['data_fi'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <div class="row text-center mb-4">
        <div class="col">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <h4 class="card-title">Total d’Accessos</h4>
                    <p class="display-5"><?= $totalAccessos ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row my-4">
        <div class="col-md-6">
            <h5 class="text-center">Usuaris més actius</h5>
            <canvas id="usuarisChart"></canvas>
        </div>
        <div class="col-md-6">
            <h5 class="text-center">Pàgines més visitades</h5>
            <canvas id="paginesChart"></canvas>
        </div>
    </div>

    <div class="row my-4">
        <div class="col">
            <h5 class="text-center">Accessos per dia</h5>
            <canvas id="diesChart"></canvas>
        </div>
    </div>

    <h3 class="mt-5">Últims accessos</h3>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-primary text-center">
                <tr>
                    <th>Data</th>
                    <th>Usuari</th>
                    <th>URL</th>
                    <th>Navegador</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= isset($log['timestamp']) ? $log['timestamp']->toDateTime()->format('Y-m-d H:i:s') : '—' ?></td>
                        <td><?= htmlspecialchars($log['usuari'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($log['url'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($log['navegador'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    const usuarisData = <?= json_encode(iterator_to_array($usuarisMesActius)) ?>;
    const paginesData = <?= json_encode(iterator_to_array($paginesMesVisitades)) ?>;
    const diesData = <?= json_encode(iterator_to_array($accesosPerDia)) ?>;

    new Chart(document.getElementById('usuarisChart'), {
        type: 'bar',
        data: {
            labels: usuarisData.map(e => e._id ?? '—'),
            datasets: [{
                label: 'Accessos',
                data: usuarisData.map(e => e.total ?? 0),
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('paginesChart'), {
        type: 'bar',
        data: {
            labels: paginesData.map(e => e._id ?? '—'),
            datasets: [{
                label: 'Visites',
                data: paginesData.map(e => e.total ?? 0),
                backgroundColor: 'rgba(255, 99, 132, 0.7)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('diesChart'), {
        type: 'line',
        data: {
            labels: diesData.map(e => e._id ?? '—'),
            datasets: [{
                label: 'Accessos per dia',
                data: diesData.map(e => e.total ?? 0),
                fill: true,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.3)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
<?php include '../utils/footer.php'; ?>

</body>
</html>
