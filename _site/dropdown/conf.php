<?php
function conectar(){
    $username = "string";
    $password = "epicpassword??:O";
    $servername = "localhost";
    $database = "epic_database";
    $conn = new mysqli($servername, $username, $password, $database);
    return $conn;
}

/* Cada vez que se quiera usar esta función se la deberá guardar en una variable y, a partir de esta, usar las funciones respectivas.
   Ej: query(), close(), etc. */
?>
