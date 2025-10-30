<?php

namespace arnaullfe\Verifactu\models\parts\cabecera;
use arnaullfe\Verifactu\models\extra\VerifactuFiscalIdentifier;

/**
 * Cabecera de factura (header)
 */
class VerifactuCabecera {
    /** @var VerifactuFiscalIdentifier */
    public VerifactuFiscalIdentifier $emisor;

    /** @var VerifactuFiscalIdentifier|null */
    public ?VerifactuFiscalIdentifier $representante = null;

    public function __construct(?VerifactuFiscalIdentifier $emisor = null, ?VerifactuFiscalIdentifier $representante = null) {
        if ($emisor) {
            $this->emisor = $emisor;
        }
        if ($representante) {
            $this->representante = $representante;
        }
    }

    /**
     * @return array
     */
    public function validate(): array {
        $errors = [];
        if ($this->emisor) {
            $errors = $this->emisor->validate("Cabezera emisor: ");
        } else {
            $errors[] = "Cabezera emisor: Emisor is required";
        }
        if ($this->representante) {
            $errors = $this->representante->validate("Cabezera representante: ");
        }
        return $errors;
    }

    /**
     * @return array
     */
    public function toArray(): array {
        return [
            'ObligadoEmision' => $this->emisor ? $this->emisor->toArray() : null,
            'Representante' => $this->representante ? $this->representante->toArray() : null
        ];
    }
}
