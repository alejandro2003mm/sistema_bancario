<?php
// Inicia o continúa la sesión actual del usuario
session_start();


// Si no existe la variable de sesión 'cuenta', la crea con valor null.
// Aquí se guardará el objeto de la cuenta activa.
if (!isset($_SESSION['cuenta'])) $_SESSION['cuenta'] = null;

// Si no existe 'historial', la crea como un arreglo vacío.
// Aquí se almacenarán las cuentas anteriores y sus movimientos serializados.
if (!isset($_SESSION['historial'])) $_SESSION['historial'] = [];

// Si no existe 'log', la crea como un arreglo vacío.
// Aquí se guardan los movimientos (depósitos, retiros, intereses) de la cuenta actual.
if (!isset($_SESSION['log'])) $_SESSION['log'] = [];
?>
