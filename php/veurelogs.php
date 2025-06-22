<?php
require 'vendor/autoload.php';

$client = new MongoDB\Client("mongodb+srv://a24alvsalalv:nkJAdHbuQRhz90Zc@cluster0.9pxismp.mongodb.net/");
$collection = $client->RegistreLogs->ProjecteIncidencia;

$perPagina = 15;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$skip = ($pagina - 1) * $perPagina;

$logs = $collection->find([], [
    'sort' => ['timestamp' => -1],
    'limit' => $perPagina,
    'skip' => $skip
]);

$totalLogs = $collection->countDocuments();
$totalPagines = ceil($totalLogs / $perPagina);
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-..." crossorigin="anonymous">
</head>

<style>
    .navegadorespai {
        max-width: 250px;
        word-break: break-word;
        white-space: normal;
    }
</style>

<body class="bg-light">

<div class="container my-5">
    <h1 class="text-center mb-4">Logs d'Accés (pàgina <?= $pagina ?> de <?= $totalPagines ?>)</h1>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-primary text-center">
                <tr>
                    <th>Data i Hora</th>
                    <th>Usuari</th>
                    <th>URL</th>
                    <th>Navegador</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <?php
                        if (empty($log['timestamp']) && empty($log['usuari']) && empty($log['url'])) {
                            continue;
                        }
                    ?>
                    <tr>
                        <td>
                            <?php
                                if (isset($log['timestamp']) && $log['timestamp'] instanceof MongoDB\BSON\UTCDateTime) {
                                    echo $log['timestamp']->toDateTime()->format('Y-m-d H:i:s');
                                } else {
                                    echo '—';
                                }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($log['usuari'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($log['url'] ?? '—') ?></td>
                        <td class="navegadorespai"><?= htmlspecialchars($log['navegador'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
    <nav aria-label="Paginació de logs">
        <ul class="pagination">

            <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?pagina=<?= $pagina - 1 ?>" tabindex="-1">Anterior</a>
            </li>

            <?php
            $rang = 2;
            $inici = max(1, $pagina - $rang);
            $final = min($totalPagines, $pagina + $rang);

            for ($i = $inici; $i <= $final; $i++): ?>
                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $i ?>" <?= $i == $pagina ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= $pagina >= $totalPagines ? 'disabled' : '' ?>">
                <a class="page-link" href="?pagina=<?= $pagina + 1 ?>">Següent</a>
            </li>

        </ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
</body>
</html>
