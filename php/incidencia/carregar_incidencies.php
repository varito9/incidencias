<?php
require_once '../utils/connexio.php';
require_once '../utils/logger.php';

if (isset($_SESSION['usuari']['nom'])) {
    $usuari = $_SESSION['usuari']['nom'];
    $url = $_SERVER['REQUEST_URI'];
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconegut';

    registraAcces($usuari, $url, $navegador);
}

$per_pagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $per_pagina;

$estat = $_GET['estat'] ?? '';
$tecnic = $_GET['tecnic'] ?? '';

$conditions = [];
$params = [];
$types = '';

if ($estat !== '') {
  $conditions[] = "estat = ?";
  $params[] = $estat;
  $types .= 's';
}

if ($tecnic !== '') {
  $conditions[] = "id_tecnic = ?";
  $params[] = $tecnic;
  $types .= 'i';
}

$where = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';


$queryTotal = "SELECT COUNT(*) AS total FROM INCIDENCIA $where";
$stmtTotal = $conn->prepare($queryTotal);
if ($types) $stmtTotal->bind_param($types, ...$params);
$stmtTotal->execute();
$total = $stmtTotal->get_result()->fetch_assoc()['total'];
$total_pagines = ceil($total / $per_pagina);

$query = "SELECT * FROM INCIDENCIA $where ORDER BY data DESC LIMIT ? OFFSET ?";
$params[] = $per_pagina;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

echo "<table class='table table-bordered'>";
echo "<thead><tr><th>ID</th><th>Títol</th><th>Estat</th><th>Data</th></tr></thead><tbody>";
while ($row = $result->fetch_assoc()) {
  echo "<tr>
    <td>{$row['id']}</td>
    <td>" . htmlspecialchars($row['titol']) . "</td>
    <td>{$row['estat']}</td>
    <td>{$row['data']}</td>
  </tr>";
}
echo "</tbody></table>";

echo "<nav><ul class='pagination paginacio justify-content-center'>";
for ($i = 1; $i <= $total_pagines; $i++) {
  $active = $i === $pagina ? 'active' : '';
  echo "<li class='page-item $active'><a class='page-link' href='#' data-pagina='$i'>$i</a></li>";
}
echo "</ul></nav>";

$conn->close();
?>
