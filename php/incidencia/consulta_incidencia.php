<?php
require_once '../utils/connexio.php';
require_once '../utils/utils.php';
require_once '../utils/logger.php';

if (isset($_SESSION['usuari']['nom'])) {
    $usuari = $_SESSION['usuari']['nom'];
    $url = $_SERVER['REQUEST_URI'];
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconegut';

    registraAcces($usuari, $url, $navegador);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function clear_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$email = $id_incidencia = "";
$incidencies = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email = clear_input($_POST["email"] ?? '');
        $id_incidencia = clear_input($_POST["id_incidencia"] ?? '');

        if (!$email && !$id_incidencia) {
            throw new Exception("Cal introduir un email o un ID d'incidència.");
        }

        $conn = new mysqli($servername, $username, $password, $database);
        $conn->set_charset("utf8mb4");

        $sql = "SELECT i.id_incidencia, i.descripcio, i.data_creacio, i.tipus_id, t.nom AS tipus_nom, u.nom AS usuari_nom, u.email
                FROM INCIDENCIA i
                JOIN USUARI u ON i.usuari_id = u.id_usuari
                JOIN TIPUS_INCIDENCIA t ON i.tipus_id = t.id_tipus";

        if ($email) {
            $sql .= " WHERE u.email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
        } elseif ($id_incidencia) {
            $sql .= " WHERE i.id_incidencia = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_incidencia);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $incidencies[] = $row;
        }

        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        die("S'ha produït un error: " . $e->getMessage());
    }
}
?>
<?php include '../utils/header.php'; ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta d'Incidències</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon" />
    <style>
        .container {
            margin-top: 40px;
        }
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .card-header {
            background-color: #4e73df;
            color: #fff;
            border-bottom: none;
            font-weight: 500;
            font-size: 1.25rem;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }
        .table td {
            vertical-align: middle;
            word-break: break-word;
            white-space: normal;
            max-width: 200px;
        }
        .action-button {
            text-decoration: none;
        }
        .form-control {
            border-radius: 0.5rem;
        }
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }
            .table td,
            .table th {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center mb-4">Consulta d'Incidències</h1>
    <div class="mb-4">
        <p class="text-muted">
            Per consultar l'estat d'una incidència, pots introduir <strong>l'email utilitzat</strong> o bé el <strong>ID de la incidència</strong>.
            Només cal emplenar <em>un dels dos camps</em> i prémer el botó <strong>"Cercar"</strong>.
        </p>
    </div>

    <form action="consulta_incidencia.php" method="POST" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md">
                <input type="email" class="form-control" id="email" name="email" placeholder="Correu electrònic" value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="col-md">
                <input type="number" class="form-control" id="id_incidencia" name="id_incidencia" placeholder="ID incidència" value="<?php echo htmlspecialchars($id_incidencia); ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary">Cercar</button>
            </div>
        </div>
    </form>

    <?php if (isset($incidencies) && count($incidencies) > 0): ?>
        <?php if ($id_incidencia && count($incidencies) == 1): ?>
            <?php $inc = $incidencies[0]; ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Incidència #<?php echo htmlspecialchars($inc['id_incidencia']); ?></span>
                    <a href="detall_incidencia.php?id=<?php echo htmlspecialchars($inc['id_incidencia']); ?>" class="btn btn-light btn-sm action-button">
                        Veure <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <p><strong>Tipus:</strong> <?php echo htmlspecialchars($inc['tipus_nom']); ?></p>
                    <p><strong>Descripció:</strong> <?php echo htmlspecialchars($inc['descripcio']); ?></p>
                    <p><strong>Data creació:</strong> <?php echo htmlspecialchars($inc['data_creacio']); ?></p>
                    <p><strong>Usuari:</strong> <?php echo htmlspecialchars($inc['usuari_nom']); ?> - <?php echo htmlspecialchars($inc['email']); ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-header">Resultats de la Cerca</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Incidència</th>
                                    <th>Tipus</th>
                                    <th>Descripció</th>
                                    <th class="text-center">Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incidencies as $inc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($inc['id_incidencia']); ?></td>
                                        <td><?php echo htmlspecialchars($inc['tipus_nom']); ?></td>
                                        <td><?php echo htmlspecialchars($inc['descripcio']); ?></td>
                                        <td class="text-center">
                                            <a href="detall_incidencia.php?id=<?php echo htmlspecialchars($inc['id_incidencia']); ?>" class="btn btn-primary btn-sm">
                                                Veure <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php elseif (isset($email) || isset($id_incidencia)): ?>
        <div class="alert alert-warning">No s'han trobat incidències que coincideixin amb els criteris de cerca.</div>
    <?php endif; ?>
</div>

<?php include '../utils/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
