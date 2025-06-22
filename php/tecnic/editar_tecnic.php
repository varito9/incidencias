<?php
session_start();

require_once '../utils/connexio.php';
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
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    header("Location: tecnic.php");
    exit;
}

$stmt = $conn->prepare("SELECT nom, email, administrador FROM TECNIC WHERE id_tecnic = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$tecnic = $result->fetch_assoc();
$stmt->close();

if (!$tecnic) {
    header("Location: tecnic.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $es_admin = isset($_POST["administrador"]) ? 1 : 0;

    if (!empty($nom) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("UPDATE TECNIC SET nom = ?, email = ?, administrador = ? WHERE id_tecnic = ?");
        $stmt->bind_param("ssii", $nom, $email, $es_admin, $id);

        if ($stmt->execute()) {
            header("Location: tecnic.php?editat=1");
            exit;
        } else {
            $missatge = "Error en editar el tècnic: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $missatge = "Introdueix un nom vàlid i un email vàlid.";
    }
}
?>

<?php include '../utils/header.php'; ?>

<div class="container my-5">
  <h2 class="text-center mb-4">Editar Tècnic</h2>

  <?php if (!empty($missatge)): ?>
    <div class="alert alert-warning text-center"><?= htmlspecialchars($missatge) ?></div>
  <?php endif; ?>

  <div class="card mx-auto shadow-sm" style="max-width: 500px;">
    <div class="card-body">
      <form method="POST" action="">
        <div class="mb-3">
          <label for="nom" class="form-label">Nom del tècnic</label>
          <input type="text" class="form-control" id="nom" name="nom" required value="<?= htmlspecialchars($tecnic['nom']) ?>">
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Correu electrònic</label>
          <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($tecnic['email']) ?>">
        </div>
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="administrador" name="administrador" <?= $tecnic['administrador'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="administrador">És administrador</label>
        </div>
        <div class="row g-2">
          <div class="col-6">
            <button type="submit" class="btn btn-primary w-100">
              Desar canvis
            </button>
          </div>
          <div class="col-6">
            <a href="tecnic.php" class="btn btn-secondary w-100">
              Cancel·lar
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if (isset($_GET['actualitzat'])): ?>
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
  <div id="toastGuardat" class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
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
  });
</script>
<?php endif; ?>

<?php include '../utils/footer.php'; ?>
<?php $conn->close(); ?>
