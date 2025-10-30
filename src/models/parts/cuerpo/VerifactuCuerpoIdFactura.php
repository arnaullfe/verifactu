<?php
namespace arnaullfe\Verifactu\models\parts\cuerpo;
/**
 * Identificador de factura en el cuerpo
 */
class VerifactuCuerpoIdFactura {
    public string $idEmisorFactura;
    public string $numSerieFactura;
    public \DateTime $fechaExpedicionFactura;

    public function __construct(string $idEmisorFactura, string $numSerieFactura, \DateTime $fechaExpedicionFactura) {
        $this->idEmisorFactura = $idEmisorFactura;
        $this->numSerieFactura = $numSerieFactura;
        $this->fechaExpedicionFactura = $fechaExpedicionFactura;
    }

    public function validate(string $prefix = ""): array {
        $errors = [];
        if (!$this->idEmisorFactura) {
            $errors[] = $prefix . "IDEmisorFactura is required";
        } elseif (strlen($this->idEmisorFactura) !== 9) {
            $errors[] = $prefix . "IDEmisorFactura must be 9 characters";
        }
        if (!$this->numSerieFactura) {
            $errors[] = $prefix . "NumSerieFactura is required";
        } elseif (strlen($this->numSerieFactura) > 60) {
            $errors[] = $prefix . "NumSerieFactura must be less than 60 characters";
        }
        if (!$this->fechaExpedicionFactura) {
            $errors[] = $prefix . "FechaExpedicionFactura is required";
        } elseif (!($this->fechaExpedicionFactura instanceof \DateTime)) {
            $errors[] = $prefix . "FechaExpedicionFactura must be a valid DateTime";
        }
        return $errors;
    }

    public function toArray(): array {
        return [
            'IDEmisorFactura' => $this->idEmisorFactura,
            'NumSerieFactura' => $this->numSerieFactura,
            'FechaExpedicionFactura' => $this->fechaExpedicionFactura->format('d-m-Y'),
        ];
    }
}
