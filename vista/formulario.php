<?php
/* --- CREAR CUENTA NUEVA --- */
if (isset($_POST['crear'])) { // Si el usuario envía el formulario de crear cuenta
    $titular = $_POST['titular'];            // Se obtiene el nombre del titular
    $saldo = (int)$_POST['saldo'];           // Se obtiene el saldo inicial (convertido a número entero)
    $interes = (int)$_POST['interes'];       // Se obtiene el interés (convertido a número entero)

    // Se guarda la nueva cuenta dentro del historial general (todas las cuentas creadas)
    $_SESSION['historial'][] = [
        // Se crea el objeto CuentaAhorro y se guarda serializado (para poder almacenarlo en sesión)
        'cuenta' => serialize(new CuentaAhorro($titular, $saldo, $interes)),
        // Se guarda el nombre del titular como texto
        'titular' => $titular,
        // Se inicia el registro de acciones con un primer mensaje
        'acciones' => [
            "🏦 Cuenta creada para $titular con saldo inicial de $$saldo (interés $interes%)."
        ],
        // Se marca esta cuenta como activa (para permitir operaciones)
        'activa' => true
    ];
}

/* --- OPERACIONES POR TITULAR --- */
if (isset($_POST['accion']) && isset($_POST['index'])) { // Si se ejecuta una acción sobre una cuenta específica
    $index = (int)$_POST['index'];      // Índice del historial (identifica qué cuenta se modificará)
    $monto = (int)($_POST['monto'] ?? 0); // Monto ingresado por el usuario (si no hay valor, usa 0)
    $accion = $_POST['accion'];          // Acción elegida: depositar, retirar o aplicar interés

    // Se obtiene la cuenta correspondiente del historial
    $data = $_SESSION['historial'][$index];
    // Se convierte de nuevo el objeto serializado a un objeto real de tipo CuentaAhorro
    $cuenta = unserialize($data['cuenta']);

    // Dependiendo de la acción seleccionada, se llama al método correspondiente
    switch ($accion) {
        case 'depositar':
            $msg = $cuenta->depositar($monto); // Deposita el monto y guarda el mensaje de confirmación
            break;
        case 'retirar':
            $msg = $cuenta->retirar($monto);   // Retira el monto, si el saldo lo permite
            break;
        case 'interes':
            $msg = $cuenta->aplicarInteres();  // Aplica el interés actual al saldo
            break;
    }

    // Se agrega el resultado de la operación al historial de acciones del titular
    $data['acciones'][] = $msg;

    // Se vuelve a guardar el objeto actualizado (serializado otra vez)
    $data['cuenta'] = serialize($cuenta);

    // Se actualiza la posición correspondiente dentro del historial general
    $_SESSION['historial'][$index] = $data;
}

/* --- CERRAR CUENTA --- */
if (isset($_POST['cerrar']) && isset($_POST['index'])) {
    $i = (int)$_POST['index']; // Se obtiene el índice de la cuenta a cerrar
    // Se marca la cuenta como inactiva (no se puede operar mientras esté cerrada)
    $_SESSION['historial'][$i]['activa'] = false;
}

/* --- REABRIR CUENTA --- */
if (isset($_POST['reabrir']) && isset($_POST['index'])) {
    $i = (int)$_POST['index']; // Se obtiene el índice de la cuenta a reabrir
    // Se marca la cuenta como activa otra vez (permitiendo operaciones)
    $_SESSION['historial'][$i]['activa'] = true;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Bancario</title>
    <!-- Se importa Bootstrap para estilos -->
    <link rel="stylesheet" href="../css/bootstrap-v5.3.3.css">
</head>
<body class="bg-light">

<!-- Contenedor principal -->
<div class="container mt-4 bg-white p-4 rounded shadow">
    <h3 class="text-center mb-4">💰 Sistema Bancario Modular</h3>

    <!-- 🧾 FORMULARIO NUEVA CUENTA -->
    <!-- Este formulario permite crear un nuevo titular -->
    <form method="POST" class="mb-4">
        <h5 class="text-primary">🧾 Nueva Cuenta</h5>
        <div class="row">
            <!-- Campo para ingresar el nombre del titular -->
            <div class="col-md-4">
                <input class="form-control mb-2" name="titular" placeholder="Titular" required>
            </div>

            <!-- Campo para ingresar el saldo inicial -->
            <div class="col-md-4">
                <input class="form-control mb-2" name="saldo" placeholder="Saldo inicial" required type="number">
            </div>

            <!-- Campo para ingresar el interés -->
            <div class="col-md-4">
                <input class="form-control mb-2" name="interes" placeholder="Interés %" value="5" required type="number">
            </div>
        </div>

        <!-- Botón que envía el formulario para crear la nueva cuenta -->
        <button name="crear" class="btn btn-primary w-100">Crear cuenta</button>
    </form>

    <!-- 📜 LISTADO DE CUENTAS (Historial de titulares creados) -->
    <?php if (!empty($_SESSION['historial'])): ?> <!-- Si hay cuentas en el historial -->
        <?php foreach (array_reverse($_SESSION['historial'], true) as $i => $h): ?> <!-- Se recorre cada cuenta -->
            
            <!-- Tarjeta que representa una cuenta (su color cambia si está activa o cerrada) -->
            <div class="border p-3 mb-3 rounded <?= $h['activa'] ? 'bg-light' : 'bg-secondary-subtle'; ?>">
                <?php
                // Se obtiene el objeto de cuenta deserializado (para poder acceder a sus métodos)
                $cuenta = unserialize($h['cuenta']);
                ?>
                <!-- Se muestra el titular y su saldo actual -->
                <h6><?= $cuenta->mostrarSaldo(); ?></h6>
                <hr>

                <!-- 🧾 HISTORIAL DE MOVIMIENTOS -->
                <!-- Se recorren y muestran todos los movimientos realizados por este titular -->
                <?php foreach ($h['acciones'] as $a): ?>
                    <p class="mb-1"><?= htmlspecialchars($a) ?></p>
                <?php endforeach; ?>
                <hr>

                <!-- 💵 FORMULARIO DE OPERACIONES (solo si la cuenta está activa) -->
                <?php if ($h['activa']): ?>
                    <form method="POST" class="mb-3">
                        <div class="row">
                            <!-- Campo para ingresar el monto de la operación -->
                            <div class="col-md-4">
                                <input class="form-control" name="monto" type="number" placeholder="Monto">
                                <!-- Campo oculto con el índice del titular en el historial -->
                                <input type="hidden" name="index" value="<?= $i ?>">
                            </div>

                            <!-- Botones para realizar cada tipo de operación -->
                            <div class="col-md-8 d-flex gap-2">
                                <button name="accion" value="depositar" class="btn btn-success w-100">Depositar</button>
                                <button name="accion" value="retirar" class="btn btn-danger w-100">Retirar</button>
                                <button name="accion" value="interes" class="btn btn-info w-100">Aplicar interés</button>
                            </div>
                        </div>
                    </form>

                    <!-- Botón para cerrar la cuenta actual -->
                    <form method="POST">
                        <input type="hidden" name="index" value="<?= $i ?>">
                        <button name="cerrar" class="btn btn-outline-secondary w-100">🔒 Cerrar cuenta</button>
                    </form>
                <?php else: ?>
                    <!-- Si la cuenta está cerrada, muestra el botón para reabrirla -->
                    <form method="POST">
                        <input type="hidden" name="index" value="<?= $i ?>">
                        <button name="reabrir" class="btn btn-outline-primary w-100">🔁 Reabrir cuenta</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
