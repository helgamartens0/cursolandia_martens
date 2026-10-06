<?php
session_start();

$_SESSION = [];      // vaciamos los datos de la sesión
session_destroy();   // y la eliminamos

header('Location: ../index.php'); //volvemos a la pagina de inicio 
exit;