<?php
namespace arnaullfe\Verifactu\models;

use arnaullfe\Verifactu\models\parts\cabecera\VerifactuCabecera;
use arnaullfe\Verifactu\models\parts\cuerpo\VerifactuCuerpo;

/**
 * Factura Verifactu
 */
class VerifactuFactura {
    public VerifactuCabecera $cabecera;
    public VerifactuCuerpo $cuerpo;

    public function __construct(VerifactuCabecera $cabecera, VerifactuCuerpo $cuerpo) {
        $this->cabecera = $cabecera;
        $this->cuerpo = $cuerpo;
    }

    public function validate(): array {
        $errors = [];
        if ($this->cabecera) {
            $errors = $this->cabecera->validate();
        } else {
            $errors[] = "Cabecera is required";
        }
        if ($this->cuerpo) {
            $errors = $this->cuerpo->validate();
        } else {
            $errors[] = "Cuerpo is required";
        }
        return $errors;
    }

    public function toArray(): array {
        return [
            'Cabecera' => $this->cabecera->toArray(),
            'RegistroFactura' => [
                'RegistroAlta' => $this->cuerpo->toArray(),
            ],
        ];
    }
}
