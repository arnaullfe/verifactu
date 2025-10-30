<?php
namespace arnaullfe\Verifactu\models\parts\cuerpo;

use arnaullfe\Verifactu\models\extra\VerifactuTipoFactura;

/**
 * Cuerpo de la factura
 */
class VerifactuCuerpo {
    public string $idVersion = '1.0';
    public VerifactuCuerpoIdFactura $idFactura;
    public string $nombreRazonEmisor;
    public string $tipoFactura;
    public string $descripcionOperacion;
    /** @var VerifactuFiscalIdentifier[]|VerifactuForeignFiscalIdentifier[] */
    public array $destinatarios;
    public float $cuotaTotal;
    public float $importeTotal;
    public $sistemaInformatico; // keep flexible due to external edit
    /** @var VerifactuCuerpoDesglose[] */
    public array $desglose;
    public string $tipoHuella = '01';
    public ?VerifactuCuerpoRegistroAnterior $registroAnterior = null;

    public function validate(): array {
        $errors = [];
        if ($this->idFactura) {
            $errors = $this->idFactura->validate("Cuerpo idFactura: ");
        } else {
            $errors[] = "Cuerpo idFactura: IDFactura is required";
        }
        if (!$this->nombreRazonEmisor) {
            $errors[] = "Cuerpo nombreRazonEmisor: NombreRazonEmisor is required";
        } elseif (strlen($this->nombreRazonEmisor) > 120) {
            $errors[] = "Cuerpo nombreRazonEmisor: NombreRazonEmisor must be less than 120 characters";
        }
        if (!$this->tipoFactura) {
            $errors[] = "Cuerpo tipoFactura: TipoFactura is required";
        }
        if (!$this->destinatarios) {
            $errors[] = "Cuerpo destinatarios: Destinatarios is required";
        } elseif (count($this->destinatarios) === 0) {
            $errors[] = "Cuerpo destinatarios: Destinatarios must be an array with at least one element";
        } else {
            $destinatariosErrors = [];
            for ($index = 0; $index < count($this->destinatarios); $index++) {
                $destinatario = $this->destinatarios[$index];
                if (method_exists($destinatario, 'validate')) {
                    $destinatariosErrors = array_merge($destinatariosErrors, $destinatario->validate("Cuerpo destinatarios: " . $index . ": "));
                }
            }
            if (count($destinatariosErrors) > 0) {
                $errors = array_merge($errors, $destinatariosErrors);
            }
        }
        if (!isset($this->cuotaTotal)) {
            $errors[] = "Cuerpo cuotaTotal: CuotaTotal is required";
        }
        if (!isset($this->importeTotal)) {
            $errors[] = "Cuerpo importeTotal: ImporteTotal is required";
        }
        if (!$this->sistemaInformatico) {
            $errors[] = "Cuerpo sistemaInformatico: SistemaInformatico is required";
        }
        if (!$this->desglose) {
            $errors[] = "Cuerpo desglose: Desglose is required";
        } elseif (count($this->desglose) === 0) {
            $errors[] = "Cuerpo desglose: Desglose must be an array with at least one element";
        } else {
            $desgloseErrors = [];
            for ($index = 0; $index < count($this->desglose); $index++) {
                $desglose = $this->desglose[$index];
                if (method_exists($desglose, 'validate')) {
                    $desgloseErrors = array_merge($desgloseErrors, $desglose->validate("Cuerpo desglose linea " . ($index + 1) . ": "));
                }
            }
            if (count($desgloseErrors) > 0) {
                $errors = array_merge($errors, $desgloseErrors);
            }
        }
        return $errors;
    }

    public function toArray(): array {
        $encadenamiento = [];
        if ($this->registroAnterior) {
            $encadenamiento = [
                'RegistroAnterior' => $this->registroAnterior->toArray(),
            ];
        } else {
            $encadenamiento = [
                'PrimerRegistro' => 'S',
            ];
        }
        return [
            'IDVersion' => $this->idVersion,
            'IDFactura' => $this->idFactura->toArray(),
            'NombreRazonEmisor' => $this->nombreRazonEmisor,
            'TipoFactura' => $this->tipoFactura,
            'DescripcionOperacion' => $this->descripcionOperacion,
            'Destinatarios' => array_map(fn($d) => $d->toArray(), $this->destinatarios),
            'CuotaTotal' => $this->cuotaTotal,
            'ImporteTotal' => $this->importeTotal,
            'SistemaInformatico' => method_exists($this->sistemaInformatico, 'toArray') ? $this->sistemaInformatico->toArray() : $this->sistemaInformatico,
            'Desglose' => [
                'DetalleDesglose' => array_map(fn($dg) => $dg->toArray(), $this->desglose),
            ],
            'TipoHuella' => $this->tipoHuella,
            'Huella' => $this->calculateHuella(),
            'Encadenamiento' => $encadenamiento,
            'FechaHoraHusoGenRegistro' => (new \DateTime())->format('c'),
        ];
    }

    public function calculateHuella(): string {
        $payload = 'IDEmisorFactura=' . $this->idFactura->idEmisorFactura
            . '&NumSerieFactura=' . $this->idFactura->numSerieFactura
            . '&FechaExpedicionFactura=' . $this->idFactura->fechaExpedicionFactura->format('d-m-Y')
            . '&TipoFactura=' . $this->tipoFactura
            . '&CuotaTotal=' . $this->cuotaTotal
            . '&ImporteTotal=' . $this->importeTotal
            . '&Huella=' . ($this->registroAnterior ? $this->registroAnterior->huella : '')
            . '&FechaHoraHusoGenRegistro=' . (new \DateTime())->format('c');
        return strtoupper(hash('sha256', $payload));
    }
}
