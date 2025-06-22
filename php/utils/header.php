<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Gestió d'Incidències</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon" />
    <link rel="stylesheet" href="../css/estils.css?v=<?= time() ?>">


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
        <a class="navbar-brand fw-bold text-primary" href="#">Gestor d'Incidencies</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="../utils/dashboard.php" class="btn btn-outline-primary btn-sm">Inici</a>
            <a href="../utils/logout.php" class="btn btn-outline-primary btn-sm">Tancar sessió</a>
        </div>
    </div>
</nav>