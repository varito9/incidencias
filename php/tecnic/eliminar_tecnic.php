<?php
session_start(); 
require_once '../utils/connexio.php';

if (!isset($_SESSION['usuari']['rol']) || $_SESSION['usuari']['rol'] !== 'admin') {
    header("Location: tecnic.php?error=permís");
    exit;
}

if (isset($_POST['id_tecnic'])) {
    $id = intval($_POST['id_tecnic']);

    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM INCIDENCIA WHERE tecnic_id = ?");
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $stmtCheck->bind_result($num_incidencies);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($num_incidencies > 0) {
        header("Location: tecnic.php?error=1");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM TECNIC WHERE id_tecnic = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: tecnic.php?eliminat=1");
    } else {
        echo "Error en eliminar el tècnic: " . $conn->error;
    }

    $stmt->close();
} else {
    header("Location: tecnic.php");
    exit;
}

$conn->close();
