<?php
session_start();

// Si no hay nadie con sesión iniciada, lo mandamos a la pantalla de inicio
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit;
}

//esto seria para q sepan quien entro, lo agregamos en las vistas dspp