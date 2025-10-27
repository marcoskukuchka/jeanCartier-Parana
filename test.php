<?php

require_once './conn/loader.php';
$sql = "SELECT * FROM webClientes";
$stmt = $dbh->prepare($sql);

$stmt->execute();
pr($stmt->fetchAll(PDO::FETCH_ASSOC));