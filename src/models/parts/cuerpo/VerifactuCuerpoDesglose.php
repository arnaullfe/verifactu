<?php
namespace arnaullfe\Verifactu\models\parts\cuerpo;


use arnaullfe\Verifactu\models\extra\VerifactuTaxType;
use arnaullfe\Verifactu\models\extra\VerifactuRegimeType;
use arnaullfe\Verifactu\models\extra\VerifactuOperationType;
/**
 * Detalle de desglose
 * @field DetalleDesglose
 */
class VerifactuCuerpoDesglose {
    public string $tipoImpuesto;
    public string $claveRegimen;
    public string $calificacionOperacion;
    public string $baseImponibleOimporteNoSujeto;
    public ?string $tipoImpositivo = null;
    public ?string $cuotaRepercutida = null;

    public function __construct($baseImponibleOimporteNoSujeto, $cuotaRepercutida, $tipoImpositivo = '21.00',$tipoImpuesto = VerifactuTaxType::IVA, $claveRegimen = VerifactuRegimeType::C01, $calificacionOperacion = VerifactuOperationType::Subject) {
        $this->tipoImpuesto = $tipoImpuesto;
        $this->claveRegimen = $claveRegimen;
        $this->calificacionOperacion = $calificacionOperacion;
        $this->baseImponibleOimporteNoSujeto = $baseImponibleOimporteNoSujeto;
        $this->tipoImpositivo = $tipoImpositivo;
        $this->cuotaRepercutida = $cuotaRepercutida;
    }

    public function validate(string $prefix = ""): array {
        $errors = [];
        if (!$this->tipoImpuesto) {
            $errors[] = $prefix . "TipoImpuesto is required";
        }
        if (!$this->claveRegimen) {
            $errors[] = $prefix . "ClaveRegimen is required";
        }
        if (!$this->calificacionOperacion) {
            $errors[] = $prefix . "CalificacionOperacion is required";
        }
        if (!$this->baseImponibleOimporteNoSujeto) {
            $errors[] = $prefix . "BaseImponibleOimporteNoSujeto is required";
        }
        if (!$this->tipoImpositivo) {
            $errors[] = $prefix . "TipoImpositivo is required";
        }
        if (!$this->cuotaRepercutida) {
            $errors[] = $prefix . "CuotaRepercutida is required";
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
        if (in_array($this->calificacionOperacion, [VerifactuOperationType::Subject, VerifactuOperationType::PassiveSubject], true)) {
            if ($this->tipoImpositivo === null) {
                return "TipoImpositivo is required for subject operation types";
            }
            if ($this->cuotaRepercutida === null) {
                return "CuotaRepercutida is required for subject operation types";
            }
        } else {
            if ($this->tipoImpositivo !== null) {
                return "TipoImpositivo cannot be defined for non-subject or exempt operation types";
            }
            if ($this->cuotaRepercutida !== null) {
                return "CuotaRepercutida cannot be defined for non-subject or exempt operation types";
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
            return "Expected tax amount of $best, got $taxAmountValue";
        }
        return null;
    }

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
