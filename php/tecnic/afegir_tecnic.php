<?php
session_start(); 
require_once '../utils/connexio.php';
require_once '../utils/auth.php';
require_once '../utils/logger.php';

if (isset($_SESSION['usuari']['nom'])) {
    $usuari = $_SESSION['usuari']['nom'];
    $url = $_SERVER['REQUEST_URI'];
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconegut';

    registraAcces($usuari, $url, $navegador);
}

if (!isset($_SESSION['usuari']['rol']) || $_SESSION['usuari']['rol'] !== 'admin') {
    header("Location: tecnic.php?error=permís");
    exit;
}

$missatge = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $contrasenya = $_POST["contrasenya"];
    $es_admin = isset($_POST["administrador"]) ? 1 : 0;

    if (!empty($nom) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($contrasenya)) {
        $contrasenyaHashed = password_hash($contrasenya, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO TECNIC (nom, email, contrasenya, administrador) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $nom, $email, $contrasenyaHashed, $es_admin);

        if ($stmt->execute()) {
            header("Location: tecnic.php?afegit=1");
            exit;
        } else {
            $missatge = "Error en afegir el tècnic: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $missatge = "Introdueix un nom, contrasenya i email vàlids.";
    }
}
?>

<?php include '../utils/header.php'; ?>

<h2 class="text-center mb-4">Afegir un Nou Tècnic</h2>

<?php if (!empty($missatge)): ?>
  <div class="alert alert-warning text-center"><?= htmlspecialchars($missatge) ?></div>
<?php endif; ?>

<div class="card mx-auto shadow-sm" style="max-width: 500px;">
  <div class="card-body">
    <form method="POST" action="" novalidate>
      <div class="mb-3">
        <label for="nom" class="form-label">Nom del tècnic</label>
        <input type="text" class="form-control" id="nom" name="nom" required>
        <div class="invalid-feedback">El nom és obligatori.</div>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Correu electrònic</label>
        <input type="email" class="form-control" id="email" name="email" required>
        <div class="invalid-feedback">Introdueix un correu electrònic vàlid.</div>
      </div>
      <div class="mb-3">
        <label for="contrasenya" class="form-label">Contrasenya</label>
        <input type="password" class="form-control" id="contrasenya" name="contrasenya" required>
        <div class="invalid-feedback">La contrasenya és obligatòria.</div>
      </div>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="administrador" name="administrador">
        <label class="form-check-label" for="administrador">És administrador</label>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-person-plus-fill"></i> Afegir
        </button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.querySelector("form").addEventListener("submit", function(e) {
    const form = e.target;
    let valid = true;

    const nom = form.querySelector("#nom");
    const email = form.querySelector("#email");
    const contrasenya = form.querySelector("#contrasenya");

    if (!nom.value.trim()) {
      nom.classList.add("is-invalid");
      valid = false;
    } else {
      nom.classList.remove("is-invalid");
    }

    if (!email.checkValidity()) {
      email.classList.add("is-invalid");
      valid = false;
    } else {
      email.classList.remove("is-invalid");
    }

    if (!contrasenya.value.trim()) {
      contrasenya.classList.add("is-invalid");
      valid = false;
    } else {
      contrasenya.classList.remove("is-invalid");
    }

    if (!valid) e.preventDefault();
  });
</script>

<?php include '../utils/footer.php'; ?>
<?php $conn->close(); ?>
