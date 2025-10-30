<?php
namespace arnaullfe\Verifactu\Models;

use arnaullfe\Verifactu\Models\TipoImpuesto;
use arnaullfe\Verifactu\Models\TipoRegimen;
use arnaullfe\Verifactu\Models\TipoOperacion;

/**
 * Representa una línea de desglose de impuestos en la factura
 * @field DetalleDesglose
 */
class LineaFactura {
    public string $tipoImpuesto;
    public string $claveRegimen;
    public string $calificacionOperacion;
    public string $baseImponibleOimporteNoSujeto;
    public ?string $tipoImpositivo = null;
    public ?string $cuotaRepercutida = null;

    public function __construct($baseImponibleOimporteNoSujeto, $cuotaRepercutida, $tipoImpositivo = '21.00',$tipoImpuesto = TipoImpuesto::IVA, $claveRegimen = TipoRegimen::C01, $calificacionOperacion = TipoOperacion::Subject) {
        $this->tipoImpuesto = $tipoImpuesto;
        $this->claveRegimen = $claveRegimen;
        $this->calificacionOperacion = $calificacionOperacion;
        $this->baseImponibleOimporteNoSujeto = $baseImponibleOimporteNoSujeto;
        $this->tipoImpositivo = $tipoImpositivo;
        $this->cuotaRepercutida = $cuotaRepercutida;
    }

    /**
     * Valida los datos de la línea de factura
     * @param string $prefix Prefijo para los mensajes de error
     * @return array Lista de errores encontrados
     */
    public function validate(string $prefix = ""): array {
        $errors = [];
        if (!$this->tipoImpuesto) {
            $errors[] = $prefix . "El tipo de impuesto es obligatorio";
        }
        if (!$this->claveRegimen) {
            $errors[] = $prefix . "La clave de régimen es obligatoria";
        }
        if (!$this->calificacionOperacion) {
            $errors[] = $prefix . "La calificación de operación es obligatoria";
        }
        if (!$this->baseImponibleOimporteNoSujeto) {
            $errors[] = $prefix . "La base imponible o importe no sujeto es obligatorio";
        }
        if (!$this->tipoImpositivo) {
            $errors[] = $prefix . "El tipo impositivo es obligatorio";
        }
        if (!$this->cuotaRepercutida) {
            $errors[] = $prefix . "La cuota repercutida es obligatoria";
        }
        $operationTypeError = $this->validateOperationType();
        if ($operationTypeError) {
            $errors[] = $prefix . $operationTypeError;
        }
        $taxAmountError = $this->validateTaxAmount();
        if ($taxAmountError) {
            $errors[] = $prefix . $taxAmountError;
        }
        return $errors;
    }

    public function validateOperationType(): ?string {
        if (!isset($this->calificacionOperacion)) {
            return null;
        }
        if (in_array($this->calificacionOperacion, [TipoOperacion::Subject, TipoOperacion::PassiveSubject], true)) {
            if ($this->tipoImpositivo === null) {
                return "El tipo impositivo es obligatorio para operaciones sujetas";
            }
            if ($this->cuotaRepercutida === null) {
                return "La cuota repercutida es obligatoria para operaciones sujetas";
            }
        } else {
            if ($this->tipoImpositivo !== null) {
                return "El tipo impositivo no debe definirse para operaciones no sujetas o exentas";
            }
            if ($this->cuotaRepercutida !== null) {
                return "La cuota repercutida no debe definirse para operaciones no sujetas o exentas";
            }
        }
        return null;
    }

    public function validateTaxAmount(): ?string {
        if (
            !isset($this->baseImponibleOimporteNoSujeto)
            || $this->tipoImpositivo === null
            || $this->cuotaRepercutida === null
        ) {
            return null;
        }
        $base = floatval($this->baseImponibleOimporteNoSujeto);
        $rate = floatval($this->tipoImpositivo);
        $taxAmountValue = $this->cuotaRepercutida;
        $bestTaxAmount = $base * ($rate / 100);
        $tolerances = [0, -0.01, 0.01, -0.02, 0.02];
        $validTaxAmount = false;
        foreach ($tolerances as $tolerance) {
            $expected = number_format($bestTaxAmount + $tolerance, 2, '.', '');
            if ($taxAmountValue === $expected) {
                $validTaxAmount = true;
                break;
            }
        }
        if (!$validTaxAmount) {
            $best = number_format($bestTaxAmount, 2, '.', '');
            return "La cuota esperada es $best, pero se ha proporcionado $taxAmountValue";
        }
        return null;
    }

    /**
     * Convierte la línea de factura a formato array
     * @return array
     */
    public function toArray(): array {
        return [
            'Impuesto' => $this->tipoImpuesto,
            'ClaveRegimen' => $this->claveRegimen,
            'CalificacionOperacion' => $this->calificacionOperacion,
            'BaseImponibleOimporteNoSujeto' => $this->baseImponibleOimporteNoSujeto,
            'TipoImpositivo' => $this->tipoImpositivo,
            'CuotaRepercutida' => $this->cuotaRepercutida
        ];
    }
}
