<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function noRuta() {
    if (!isset($_SESSION['rol'])) {
        header("Location: ../index.php");
        exit;
    }
}

function is_logged_in() {
    return isset($_SESSION['usuari']);
}

function get_user_role() {
    return $_SESSION['usuari']['rol'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: ../utils/login.php");
        exit;
    }
}

function require_role($role) {
    require_login();
    if (get_user_role() !== $role) {
        header("HTTP/1.1 403 Forbidden");
        echo "<h1>403 - Accés denegat</h1>";
        echo "<p>No tens permisos per accedir a aquesta pàgina.</p>";
        exit;
    }
}
