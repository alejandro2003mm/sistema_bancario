<?php 
// Clase base que representa una cuenta bancaria general
class CuentaBancaria {
    protected $titular; // Nombre del titular
    protected $saldo;   // Saldo actual de la cuenta

    // Constructor: inicializa titular y saldo
    public function __construct($titular, $saldoInicial) {
        $this->titular = $titular;
        $this->saldo = (int)$saldoInicial;
    }

    // Deposita una cantidad si es válida y actualiza el saldo
    public function depositar($cantidad) {
        if ($cantidad > 0) {
            $this->saldo += $cantidad;
            return "💵 Depósito de $$cantidad realizado. Nuevo saldo: $$this->saldo";
        }
        return "❌ Error: cantidad inválida.";
    }

    // Retira una cantidad si hay suficiente saldo
    public function retirar($cantidad) {
        if ($cantidad > 0 && $cantidad <= $this->saldo) {
            $this->saldo -= $cantidad;
            return "💸 Retiro de $$cantidad realizado. Saldo restante: $$this->saldo";
        }
        return "⚠️ Saldo insuficiente o cantidad inválida.";
    }

    // Devuelve una cadena con el titular y el saldo actual
    public function mostrarSaldo() {
        return "👤 Titular: {$this->titular} | Saldo actual: $$this->saldo";
    }
}

// Clase hija que hereda de CuentaBancaria y agrega interés
class CuentaAhorro extends CuentaBancaria {
    protected $interes; // Porcentaje de interés

    // Constructor: usa el del padre y agrega el interés
    public function __construct($titular, $saldoInicial, $interes) {
        parent::__construct($titular, $saldoInicial);
        $this->interes = $interes;
    }

    // Aplica el interés al saldo actual y devuelve el resultado
    public function aplicarInteres() {
        $ganancia = $this->saldo * $this->interes / 100;
        $this->saldo += $ganancia;
        return "📈 Interés aplicado ({$this->interes}%). Ganancia: $$ganancia. Nuevo saldo: $$this->saldo";
    }
}
?>
