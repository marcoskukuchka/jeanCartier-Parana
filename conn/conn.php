<?php
$dbuser = 'AGSWEB';
$dbpass = 'AgsWeb2025!';
try {

    $dsn = "sqlsrv:Server=181.13.218.11, 10028;Database=siv"; //conexion vpn

    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    );
    $dbh = new PDO($dsn, $dbuser, $dbpass);
} catch (PDOException $e) {
    echo $e->getMessage();
    header('Location: 404.php?error=db');
}

$serverName = "181.13.218.11, 10028";
$connectionInfo = array("Database" => "Siv", "UID" => "AGSWEB", "PWD" => "AgsWeb2025!", "ConnectionPooling" => "1");
$conmsql = sqlsrv_connect($serverName, $connectionInfo);