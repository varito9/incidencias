<?php
require_once '../utils/connexio.php';

$id_incidencia = $_POST['id_incidencia'] ?? null;

if ($id_incidencia === null || !is_numeric($id_incidencia)) {
    die("ID de la incidència no proporcionat o invàlid.");
}

$sql = "UPDATE INCIDENCIA SET eliminat = 1 WHERE id_incidencia = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error en la preparació de la consulta SQL: " . $conn->error);
}

$stmt->bind_param("i", $id_incidencia);

if ($stmt->execute()) {
    header("Location: incidencia.php?eliminada=1");
    exit;
} else {
    echo "<div class='alert alert-danger'>Error en marcar la incidència com eliminada.</div>";
}

$stmt->close();
$conn->close();
?>
