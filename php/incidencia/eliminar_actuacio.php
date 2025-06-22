<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../utils/connexio.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_actuacio = $_POST['id_actuacio'] ?? null;
    $id_incidencia = $_POST['id_incidencia'] ?? null;

    if ($id_actuacio && $id_incidencia) {
        $stmt = $conn->prepare("DELETE FROM ACTUACIO WHERE id_actuacio = ?");
        $stmt->bind_param("i", $id_actuacio);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: documentar_incidencia.php?id=" . $id_incidencia);
    exit;
} else {
    echo "Accés no vàlid.";
}
?>
