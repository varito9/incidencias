<?php
require_once 'utils/connexio.php';

$usuaris = [
    ['email' => 'a24bryruzgon@inspedralbes.cat', 'password' => '1234'],
    ['email' => 'a24alvsalalv@inspedralbes.cat', 'password' => '1234'],
    ['email' => 'lmartinez@empresa.com', 'password' => '1234'],
    ['email' => 'jperez@empresa.com', 'password' => '1234'],
];

foreach ($usuaris as $usuari) {
    $hash = password_hash($usuari['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE TECNIC SET contrasenya = ? WHERE email = ?");
    $stmt->bind_param("ss", $hash, $usuari['email']);
    $stmt->execute();
}

echo "Contrasenyes actualitzades correctament.";
