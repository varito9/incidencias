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

$esAdmin = isset($_SESSION['usuari']['rol']) && $_SESSION['usuari']['rol'] === 'admin';

$sql = "SELECT
           t.id_tecnic,
           t.nom,
           t.email,
           t.administrador,
           COUNT(i.id_incidencia) AS incidencies_assignades
       FROM
           TECNIC t
       LEFT JOIN
           INCIDENCIA i ON t.id_tecnic = i.tecnic_id
       GROUP BY
           t.id_tecnic, t.nom, t.email, t.administrador";

$result = $conn->query($sql);
$tecnics = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tecnics[] = $row;
    }
}

$conn->close();
?>
<?php include '../utils/header.php'; ?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Tècnics</h2>
    <?php if ($esAdmin): ?>
      <a href="afegir_tecnic.php" class="btn btn-success btn-add">➕ <span>Afegir Tècnic</span></a>
    <?php endif; ?>
  </div>

  <?php if (isset($_GET['eliminat'])): ?>
    <div id="popupAlert" class="alert alert-success alert-dismissible fade show" role="alert">
      Tècnic eliminat correctament.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tancar"></button>
    </div>
  <?php elseif (isset($_GET['afegit'])): ?>
    <div id="popupAlert" class="alert alert-success alert-dismissible fade show" role="alert">
      Tècnic afegit correctament.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tancar"></button>
    </div>
  <?php elseif (isset($_GET['actualitzat'])): ?>
    <div id="popupAlert" class="alert alert-success alert-dismissible fade show" role="alert">
      Tècnic actualitzat correctament.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tancar"></button>
    </div>
  <?php elseif (isset($_GET['error'])): ?>
    <div id="popupAlert" class="alert alert-danger alert-dismissible fade show" role="alert">
      No es pot eliminar el tècnic perquè té incidències assignades.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tancar"></button>
    </div>
  <?php endif; ?>

  <div class="table-responsive mx-auto" style="width: 90%;">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-primary">
        <tr class="text-center">
          <th>ID</th>
          <th>Nom</th>
          <th>Email</th>
          <th>Rol</th>
          <th>Incidències</th>
          <?php if ($esAdmin): ?>
            <th>Accions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tecnics as $tecnic): ?>
          <tr>
            <td><?= $tecnic['id_tecnic'] ?></td>
            <td><?= htmlspecialchars($tecnic['nom']) ?></td>
            <td><?= htmlspecialchars($tecnic['email']) ?></td>
            <td class="text-center">
              <?php if ($tecnic['administrador']): ?>
                <span class="badge bg-primary">Admin</span>
              <?php else: ?>
                <span class="badge bg-secondary">Tècnic</span>
              <?php endif; ?>
            </td>
            <td class="text-center"><?= $tecnic['incidencies_assignades'] ?></td>
            <?php if ($esAdmin): ?>
              <td class="text-center">
                <a href="editar_tecnic.php?id=<?= $tecnic['id_tecnic'] ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar" data-bs-toggle="modal" data-bs-target="#confirmModal" data-id="<?= $tecnic['id_tecnic'] ?>">Eliminar</button>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($esAdmin): ?>
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="eliminar_tecnic.php">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="confirmLabel">Confirmar eliminació</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tancar"></button>
        </div>
        <div class="modal-body">
          Segur que vols eliminar aquest tècnic? Aquesta acció no es pot desfer.
        </div>
        <div class="modal-footer">
          <input type="hidden" name="id_tecnic" id="tecnicIdEliminar">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel·lar</button>
          <button type="submit" class="btn btn-danger">Eliminar</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const alertBox = document.getElementById("popupAlert");
    if (alertBox) {
      setTimeout(() => {
        const alert = new bootstrap.Alert(alertBox);
        alert.close();
      }, 4000);
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
    const confirmModal = document.getElementById('confirmModal');
    const inputId = document.getElementById('tecnicIdEliminar');
    const buttons = document.querySelectorAll('.btn-eliminar');

    buttons.forEach(button => {
      button.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        inputId.value = id;
      });
    });
  });
</script>

<?php include '../utils/footer.php'; ?>
