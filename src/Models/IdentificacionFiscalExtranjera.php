<?php

namespace arnaullfe\Verifactu\Models;
/**
 * Representa un identificador fiscal extranjero
 *
 * @field Cabecera/ObligadoEmision
 * @field Cabecera/Representante
 * @field RegistroAlta/Tercero
 * @field IDDestinatario
 */
class IdentificacionFiscalExtranjera {
    /**
     * Razón social o nombre completo
     * @field NombreRazon
     * @var string
     */
    public string $name;

    /**
     * Código ISO del país (formato de 2 letras)
     * @field IDOtro/CodigoPais
     * @var string
     */
    public string $country;

    /**
     * Tipo de identificación según el país
     * @field IDOtro/IDType
     * @var string
     */
    public string $type;

    /**
     * Valor del identificador fiscal extranjero
     * @field IDOtro/ID
     * @var string
     */
    public string $value;

    /**
     * Crea una nueva instancia de identificador fiscal extranjero
     * @param string $name Nombre o razón social
     * @param string $country Código de país ISO 3166-1 alpha-2
     * @param string $type Tipo de identificación
     * @param string $value Número de identificación
     */
    public function __construct(string $name, string $country, string $type, string $value) {
        $this->name = $name;
        $this->country = $country;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Valida los datos del identificador fiscal extranjero
     * @param string $prefix Prefijo para los mensajes de error
     * @return array Lista de errores encontrados
     */
    public function validate(string $prefix = ""): array {
        $errors = [];
        if (!$this->name || trim($this->name) === '') {
            $errors[] = $prefix . "El nombre es obligatorio";
        }
        if (strlen($this->name) > 120) {
            $errors[] = $prefix . "El nombre no puede exceder 120 caracteres";
        }
        if (!$this->country || !preg_match('/^[A-Z]{2}$/', $this->country)) {
            $errors[] = $prefix . "El código de país debe ser de 2 letras mayúsculas (ISO 3166-1)";
        }
        if ($this->country === "ES") {
            $errors[] = $prefix . 'El código de país no puede ser "ES", use IdentificacionFiscal para identificadores españoles';
        }
        if (!$this->type || trim($this->type) === '') {
            $errors[] = $prefix . "El tipo de identificación es obligatorio";
        }
        if (!$this->value || trim($this->value) === '') {
            $errors[] = $prefix . "El número de identificación es obligatorio";
        }
        if (strlen($this->value) > 20) {
            $errors[] = $prefix . "El número de identificación no puede exceder 20 caracteres";
        }
        return $errors;
    }

    /**
     * Transforma el identificador a formato array
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
