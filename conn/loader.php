<?php
//$includes = ['conn.php', 'functions.php', 'sql.php', 'array_group_by.php'];
$includes = ['session.php', 'functions.php', 'conn.php', 'sql.php', 'array_group_by.php'];
foreach ($includes as $file) {
    $path = __DIR__ . "/$file";
    if (file_exists($path)) {
        require_once $path;
    } else {
        die("Error: No se encontró el archivo $file");
    }
}
