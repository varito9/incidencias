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
    header("Location: login.php");
    exit;
}

$usuari = $_SESSION['usuari'];
$nom = $usuari['nom'];
$rol = $usuari['rol'];

require_once '../utils/connexio.php';

$seccions = [
    'usuari' => [
        ['label' => 'Crear Incidència', 'link' => '../incidencia/form.php', 'icon' => 'bi-plus-circle'],
        ['label' => 'Consultar Incidència', 'link' => '../incidencia/consulta_incidencia.php', 'icon' => 'bi-search']
    ],
    'tecnic' => [
        ['label' => 'Crear Incidència', 'link' => '../incidencia/form.php', 'icon' => 'bi-plus-circle'],
        ['label' => 'Consultar Incidència', 'link' => '../incidencia/consulta_incidencia.php', 'icon' => 'bi-search'],
        ['label' => 'Llistat Incidències', 'link' => '../incidencia/incidencia.php', 'icon' => 'bi-list-check'],
        ['label' => 'Tècnics', 'link' => '../tecnic/tecnic.php', 'icon' => 'bi-tools'],
    ],
    'admin' => [
        ['label' => 'Crear Incidència', 'link' => '../incidencia/form.php', 'icon' => 'bi-plus-circle'],
        ['label' => 'Consultar Incidència', 'link' => '../incidencia/consulta_incidencia.php', 'icon' => 'bi-search'],
        ['label' => 'Llistat Incidències', 'link' => '../incidencia/incidencia.php', 'icon' => 'bi-list-check'],
        ['label' => 'Tècnics', 'link' => '../tecnic/tecnic.php', 'icon' => 'bi-tools'],
        ['label' => 'Logs', 'link' => '../logs/estadistiques.php', 'icon' => 'bi-clipboard-data']
    ]
];
$cards = $seccions[$rol];
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Gestió d'Incidències</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon" />

    <style>
        body {
            background-color: #f8f9fa;
        }

        .card-option {
            background: #fff;
            border-radius: 1.5rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        }

        .card-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .card-option i {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }

        .dashboard-header {
            margin-top: 3rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        @media (max-width: 576px) {
            .card-option {
                padding: 1.5rem;
            }

            .card-option i {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#">Intranet Incidències</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-muted">Benvingut, <?= htmlspecialchars($nom) ?></span>
            <a href="logout.php" class="btn btn-outline-primary btn-sm">Tancar sessió</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="dashboard-header">
        <h2>Hola, <?= htmlspecialchars($nom) ?>!</h2>
        <p>Gestiona les teves incidències de manera ràpida i eficient.</p>
    </div>

    <div class="row g-4">
        <?php
        $count = count($cards);
        if ($rol === 'usuari') {
            foreach ($cards as $card) {
                echo '<div class="col-12 col-sm-6">';
                renderCard($card);
                echo '</div>';
            }
        } elseif ($rol === 'tecnic') {
            for ($i = 0; $i < 4; $i++) {
                echo '<div class="col-12 col-sm-6">';
                renderCard($cards[$i]);
                echo '</div>';
            }
        } elseif ($rol === 'admin') {
            for ($i = 0; $i < 4; $i++) {
                echo '<div class="col-12 col-sm-6">';
                renderCard($cards[$i]);
                echo '</div>';
            }
            echo '<div class="col-12">';
            renderCard($cards[4]);
            echo '</div>';
        }
        ?>
    </div>
</div>

<?php
function renderCard($card) {
    echo '
        <div class="card-option h-100">
            <i class="bi ' . htmlspecialchars($card['icon']) . '"></i>
            <h5>' . htmlspecialchars($card['label']) . '</h5>
            <a href="' . htmlspecialchars($card['link']) . '" class="btn btn-outline-primary">Accedir</a>
        </div>';
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
