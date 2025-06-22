<?php
require 'vendor/autoload.php';

$client = new MongoDB\Client("mongodb+srv://a24alvsalalv:nkJAdHbuQRhz90Zc@cluster0.9pxismp.mongodb.net/");
$collection = $client->RegistreLogs->ProjecteIncidencia;

$totalLogs = $collection->countDocuments();

$usuaris = $collection->aggregate([
    ['$group' => ['_id' => '$usuari', 'total' => ['$sum' => 1]]],
    ['$sort' => ['total' => -1]]
]);

$urls = $collection->aggregate([
    ['$group' => ['_id' => '$url', 'total' => ['$sum' => 1]]],
    ['$sort' => ['total' => -1]]
]);

$ultim = $collection->findOne([], ['sort' => ['timestamp' => -1]]);
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell d'Administració</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <h1 class="text-center mb-5">Panell d'Estadístiques</h1>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary">Total de Logs</h5>
                    <p class="card-text fs-4"><?= $totalLogs ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary">Últim Accés</h5>
                    <p class="mb-0"><strong>Usuari:</strong> <?= htmlspecialchars($ultim['usuari'] ?? '—') ?></p>
                    <p class="mb-0"><strong>Data:</strong> <?= isset($ultim['timestamp']) ? $ultim['timestamp']->toDateTime()->format('Y-m-d H:i:s') : '—' ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary">Accessos per Usuari</h5>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($usuaris as $u): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($u['_id'] ?? 'Anònim') ?> — <?= $u['total'] ?> vegades
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary">Accessos per URL</h5>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($urls as $url): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars((string)($url['_id'])) ?> — <?= $url['total'] ?> vegades
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
