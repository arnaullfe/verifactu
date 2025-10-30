<?php

namespace arnaullfe\Verifactu\models\extra;
/**
 * Identificador fiscal de fuera de España
 *
 * @field Caberecera/ObligadoEmision
 * @field Caberecera/Representante
 * @field RegistroAlta/Tercero
 * @field IDDestinatario
 */
class VerifactuForeignFiscalIdentifier {
    /**
     * Nombre-razón social
     * @field NombreRazon
     * Máximo 120 caracteres. No puede estar vacío.
     * @var string
     */
    public string $name;

    /**
     * Código del país (ISO 3166-1 alpha-2 codes)
     * @field IDOtro/CodigoPais
     * @var string
     */
    public string $country;

    /**
     * Clave para establecer el tipo de identificación en el país de residencia
     * @field IDOtro/IDType
     * @var string
     */
    public string $type;

    /**
     * Número de identificación en el país de residencia
     * @field IDOtro/ID
     * Máximo 20 caracteres. No puede estar vacío.
     * @var string
     */
    public string $value;

    /**
     * Constructor
     * @param string $name
     * @param string $country
     * @param string $type
     * @param string $value
     */
    public function __construct(string $name, string $country, string $type, string $value) {
        $this->name = $name;
        $this->country = $country;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Valida el identificador fiscal extranjero
     * @param string $prefix
     * @return array
     */
    public function validate(string $prefix = ""): array {
        $errors = [];
        if (!$this->name || strlen($this->name) === 0) {
            $errors[] = $prefix . "Name must not be blank.";
        }
        if (strlen($this->name) > 120) {
            $errors[] = $prefix . "Name must not exceed 120 characters.";
        }
        if (!$this->country || !preg_match('/^[A-Z]{2}$/', $this->country)) {
            $errors[] = $prefix . "Country must be a 2-letter uppercase ISO 3166-1 alpha-2 code.";
        }
        if ($this->country === "ES") {
            $errors[] = $prefix . 'Country code cannot be "ES", use the `FiscalIdentifier` model instead';
        }
        if (!$this->type) {
            $errors[] = $prefix . "Type (IDType) must not be blank.";
        }
        if (!$this->value || strlen($this->value) === 0) {
            $errors[] = $prefix . "Value (ID) must not be blank.";
        }
        if (strlen($this->value) > 20) {
            $errors[] = $prefix . "Value (ID) must not exceed 20 characters.";
        }
        return $errors;
    }

    /**
     * Convierte el identificador a un array
     * @return array
     */
    public function toArray(): array {
        return [
            'IDOtro' => [
                'CodigoPais' => $this->country,
                'IDType' => $this->type,
                'ID' => $this->value
            ]
        ];
    }
}
