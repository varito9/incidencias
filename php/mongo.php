<?php
require 'vendor/autoload.php';

$client = new MongoDB\Client("mongodb+srv://a24alvsalalv:nkJAdHbuQRhz90Zc@cluster0.9pxismp.mongodb.net/");

$collection = $client->RegistreLogs->ProjecteIncidencia;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function UsuariAutenticat() {
    return $_SESSION['usuari'] ?? 'anònim';
}

function registrarAcces($collection) {
    $log = [
        'url' => $_SERVER['REQUEST_URI'] ?? 'desconegut',
        'usuari' => UsuariAutenticat(),
        'timestamp' => new MongoDB\BSON\UTCDateTime(),
        'navegador' => $_SERVER['HTTP_USER_AGENT'] ?? 'desconegut'
    ];

    try {
        $collection->insertOne($log);
    } catch (Exception $e) {
        error_log('Error al guardar log a MongoDB: ' . $e->getMessage());
    }
}

registrarAcces($collection);
?>