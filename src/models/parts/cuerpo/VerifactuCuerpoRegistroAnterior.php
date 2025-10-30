<?php
namespace arnaullfe\Verifactu\models\parts\cuerpo;

/**
 * Registro anterior de la factura
 */
class VerifactuCuerpoRegistroAnterior {
    public string $idEmisorFactura;
    public string $numSerieFactura;
    public \DateTime $fechaExpedicionFactura;
    public string $huella;

    public function __construct(string $idEmisorFactura, string $numSerieFactura, \DateTime $fechaExpedicionFactura, string $huella) {
        $this->idEmisorFactura = $idEmisorFactura;
        $this->numSerieFactura = $numSerieFactura;
        $this->fechaExpedicionFactura = $fechaExpedicionFactura;
        $this->huella = $huella;
    }

    public function toArray(): array {
        return [
            'IDEmisorFactura' => $this->idEmisorFactura,
            'NumSerieFactura' => $this->numSerieFactura,
            'FechaExpedicionFactura' => $this->fechaExpedicionFactura->format('d-m-Y'),
            'Huella' => $this->huella
        ];
    }
}
