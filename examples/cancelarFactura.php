<?php

use arnaullfe\Verifactu\Models\CabeceraFactura;
use arnaullfe\Verifactu\Models\CancelarFactura;
use arnaullfe\Verifactu\Models\IdentificacionFiscal;
use arnaullfe\Verifactu\Models\IdFactura;
use arnaullfe\Verifactu\Models\SistemaInformatico;
use arnaullfe\Verifactu\Services\VerifactuClient;

$cabecera = new CabeceraFactura(new IdentificacionFiscal('TECNOLOGIA AVANZADA SL', 'B87654321'));
$idFactura = new IdFactura('B87654321', 'INV-2025-001', new DateTime('2025-03-15'));
$cancelarFactura = new CancelarFactura($cabecera, $idFactura, new DateTime('2025-03-15T14:32:18+00:00'), 'A1B2C3D4E5F6A7B8C9D0E1F2A3B4C5D6E7F8A9B0C1D2E3F4A5B6C7D8E9F0A1B2C3D4');
$cancelarFactura->sistemaInformatico = new SistemaInformatico('77', 'Sistema de Gestión Empresarial', 'TECNOLOGIA AVANZADA SL', 'B87654321', '2.1', '3');
$cliente = new VerifactuClient();
$cliente->setIsProduction(false);
$path = 'ruta/al/certificado.pfx';
$cliente->setCertificate($path, 'password', true);
$res = $cliente->enviarFactura($cancelarFactura);

return $res;

