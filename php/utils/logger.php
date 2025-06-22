<?php
require_once 'connexio.php';
require '../vendor/autoload.php';

use MongoDB\Client;

function registraAcces($usuari, $url, $navegador) {
    $client = new Client("mongodb+srv://a24alvsalalv:nkJAdHbuQRhz90Zc@cluster0.9pxismp.mongodb.net/");
    $collection = $client->RegistreLogs->ProjecteIncidencia;

    $document = [
        'usuari' => $usuari,
        'url' => $url,
        'navegador' => $navegador,
        'timestamp' => new MongoDB\BSON\UTCDateTime()
    ];

    $collection->insertOne($document);
}
