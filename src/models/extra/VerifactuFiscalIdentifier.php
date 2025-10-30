<?php

namespace arnaullfe\Verifactu\models\extra;
/**
 * Identificador fiscal
 *
 * @field Caberecera/ObligadoEmision
 * @field Caberecera/Representante
 */
class VerifactuFiscalIdentifier {
    /**
     * Nombre-razón social
     * @field NombreRazon
     * Máximo 120 caracteres. No puede estar vacío.
     * @var string
     */
    public string $name;

    /**
     * Número de identificación fiscal (NIF)
     * @field NIF
     * Exactamente 9 caracteres. No puede estar vacío.
     * @var string
     */
    public string $nif;

    /**
     * Class constructor
     * @param string $name Nombre-razón social (máx. 120)
     * @param string $nif  Número de identificación fiscal (NIF, 9 caracteres)
     */
    public function __construct(string $name, string $nif) {
        $this->name = $name;
        $this->nif = $nif;
    }

    /**
     * Valida el identificador fiscal
     * @param string $prefix
     * @return array
     */
    public function validate(string $prefix = ""): array {
        $errors = [];
        if (!$this->name) {
            $errors[] = $prefix . "Name is required";
        } elseif (strlen($this->name) > 120) {
            $errors[] = $prefix . "Name must be less than 120 characters";
        }
        if (!$this->nif) {
            $errors[] = $prefix . "NIF is required";
        } elseif (strlen($this->nif) !== 9) {
            $errors[] = $prefix . "NIF must be 9 characters";
        }
        return $errors;
    }

    /**
     * Convierte el identificador fiscal a un array
     * @return array
     */
    public function toArray(): array {
        return [
            'NombreRazon' => $this->name,
            'NIF' => $this->nif
        ];
    }
}
