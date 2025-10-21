<?php

require_once './conn/loader.php';
$sql = "SELECT * FROM vueWebArticulosDisponibilidad WHERE Deposito = 1 AND Stock > 0 ORDER BY Foto ASC, Importe ASC";
$stmt = $dbh->prepare($sql);

$stmt->execute();
pr($stmt->fetchAll(PDO::FETCH_ASSOC));