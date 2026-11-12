<?php
session_start();

// Solo el administrador puede entrar a estas páginas 
if (($_SESSION['rol'] ?? '') !== 'administrador') {
    header('Location: ../index.php');
    exit;
}