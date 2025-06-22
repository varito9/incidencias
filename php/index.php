<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Inici - Gestió d'Incidències</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
      <link rel="icon" href="../utils/ico/favicon.ico?v=<?= time() ?>" type="image/x-icon">


    <style>
        body {
            height: 100%;
            margin: 0;
        }
        .intro-text {
            background-color: #f8f9fa;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .btn-login {
            border-radius: 30px;
            padding: 0.5rem 1.5rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Gestió d'Incidències</a>
        <div class="ms-auto">
            <a href="utils/login.php" class="btn btn-outline-light btn-sm">Login</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="intro-text text-center mb-5">
        <h2 class="mb-4">Benvingut a la plataforma de gestió d'incidències</h2>
        <p class="lead">
            Aquesta aplicació web té com a objectiu permetre't registrar i gestionar incidències informàtiques dins de l'organització. 
            Qualsevol membre d’un departament pot crear una nova incidència, indicant el departament d’origen i una descripció del problema que ha detectat. 
            La data es registra automàticament, i el sistema assigna un identificador únic a cada incidència.
        </p>
    </div>
    <div class="text-center">
        <a href="utils/login.php" class="btn btn-primary btn-lg btn-login">Accedeix al sistema</a>
    </div>
</div>

<?php include 'utils/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
