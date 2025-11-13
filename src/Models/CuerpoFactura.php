<?php
namespace arnaullfe\Verifactu\Models;

use arnaullfe\Verifactu\Models\TipoFactura;
use arnaullfe\Verifactu\Models\IdentificacionFiscal;
use arnaullfe\Verifactu\Models\IdentificacionFiscalExtranjera;

/**
 * Contiene el contenido principal de la factura con todos sus datos
 */
class CuerpoFactura {
    public string $idVersion = '1.0';
    public IdFactura $idFactura;
    public string $nombreRazonEmisor;
    public string $tipoFactura;
    public string $descripcionOperacion;
    /** @var IdentificacionFiscal[]|IdentificacionFiscalExtranjera[] */
    public array $destinatarios;
    public string $cuotaTotal; // TOTAL IMPUESTOS FACTURA, STRING 2 DECIMALES
    public string $importeTotal; // TOTAL FACTURA, STRING 2 DECIMALES
    public $sistemaInformatico; // Se mantiene flexible para ediciones externas
    /** @var LineaFactura[] */
    public array $desglose;
    public string $tipoHuella = '01';
    public ?RegistroAnterior $registroAnterior = null;

    /**
     * Valida todos los datos del cuerpo de la factura
     * @return array Lista de errores encontrados
     */
    public function validate(): array {
        $errors = [];
        if ($this->idFactura) {
            $errors = $this->idFactura->validate("Cuerpo idFactura: ");
        } else {
            $errors[] = "Cuerpo idFactura: El identificador de factura es obligatorio";
        }
        if (!$this->nombreRazonEmisor) {
            $errors[] = "Cuerpo nombreRazonEmisor: El nombre o razón social del emisor es obligatorio";
        } elseif (strlen($this->nombreRazonEmisor) > 120) {
            $errors[] = "Cuerpo nombreRazonEmisor: El nombre no puede exceder 120 caracteres";
        }
        if (!$this->tipoFactura) {
            $errors[] = "Cuerpo tipoFactura: El tipo de factura es obligatorio";
        }
        if (!$this->destinatarios) {
            $errors[] = "Cuerpo destinatarios: Debe haber al menos un destinatario";
        } elseif (count($this->destinatarios) === 0) {
            $errors[] = "Cuerpo destinatarios: Debe haber al menos un destinatario";
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
            $errors[] = "Cuerpo cuotaTotal: La cuota total es obligatoria";
        }
        if (!isset($this->importeTotal)) {
            $errors[] = "Cuerpo importeTotal: El importe total es obligatorio";
        }
        if (!$this->sistemaInformatico) {
            $errors[] = "Cuerpo sistemaInformatico: El sistema informático es obligatorio";
        }
        if (!$this->desglose) {
            $errors[] = "Cuerpo desglose: El desglose es obligatorio";
        } elseif (count($this->desglose) === 0) {
            $errors[] = "Cuerpo desglose: Debe haber al menos una línea en el desglose";
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

    /**
     * Convierte el cuerpo de la factura a formato array
     * @return array
     */
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

    /**
     * Calcula la huella digital de la factura según el algoritmo SHA-256
     * @return string Huella en hexadecimal mayúsculas
     */
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
