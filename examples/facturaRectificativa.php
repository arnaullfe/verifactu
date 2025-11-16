<?php

// GENERAR EMISOR

use arnaullfe\Verifactu\Models\CabeceraFactura;
use arnaullfe\Verifactu\Models\CuerpoFactura;
use arnaullfe\Verifactu\Models\Factura;
use arnaullfe\Verifactu\Models\IdentificacionFiscal;
use arnaullfe\Verifactu\Models\IdFactura;
use arnaullfe\Verifactu\Models\LineaFactura;
use arnaullfe\Verifactu\Models\RegistroAnterior;
use arnaullfe\Verifactu\Models\SistemaInformatico;
use arnaullfe\Verifactu\Models\TipoFactura;
use arnaullfe\Verifactu\Models\TipoRectificativa;
use arnaullfe\Verifactu\Services\VerifactuClient;
use arnaullfe\Verifactu\Services\VerifactuQrGenerator;

$EMISOR_NIF = "12345678A";
$EMISOR_NOMBRE = "Mi Empresa SL";
$emisor = new IdentificacionFiscal($EMISOR_NOMBRE, $EMISOR_NIF);

// GENERAR CABECERA FACTURA
$cabeceraFactura = new CabeceraFactura($emisor);
$cuerpoFactura = new CuerpoFactura();
$cuerpoFactura->idFactura = new IdFactura($EMISOR_NIF, "F-2025-2", new DateTime());
$cuerpoFactura->nombreRazonEmisor = $EMISOR_NOMBRE;
$cuerpoFactura->tipoFactura = TipoFactura::R1;
$cuerpoFactura->tipoRectificativa = TipoRectificativa::DIFERENCIAS;
$cuerpoFactura->descripcionOperacion = "Rectificación de factura";
$cuerpoFactura->fechaOperacion = new DateTime();
$cuerpoFactura->destinatarios = [new IdentificacionFiscal("Cliente SA", "87654321B")];
$cuerpoFactura->cuotaTotal = "-21.00"; // string with 2 decimals - IVA
$cuerpoFactura->importeTotal = "-121.00"; // string with 2 decimals - subtotal + IVA
$cuerpoFactura->sistemaInformatico = new SistemaInformatico("77", "Mi Sistema de Facturación", $EMISOR_NIF, $EMISOR_NIF, "1.0", "1");
$cuerpoFactura->desglose = [
  new LineaFactura("-100.00", "21.00", "21.00")
];
// solo si hay factura anterior
$cuerpoFactura->registroAnterior = new RegistroAnterior($EMISOR_NIF, "F-2025-1", new DateTime(), "HUELLA_DE_LA_FACTURA_ORIGINAL");
$factura = new Factura($cabeceraFactura, $cuerpoFactura);
$facturaArray = $factura->toArray(); // guardar en base de datos, para guardar la huella de la factura original

$client = new VerifactuClient();
$client->setIsProduction(false);
$client->setCertificate("ruta/al/certificado.pfx", "contraseña");
$respuesta = $client->enviarFactura($factura);

// VALIDAR QUE RESPUESTA ES CORRECTA, Y LUEGO GENERAR EL QR PARA MOSTRAR EN LA FACTURA PDF
if(empty($respuesta['success'])){
  throw new Exception("Error al enviar la factura: " . $respuesta['message']);
}
$qrGenerator = new VerifactuQrGenerator();
$qrGenerator->setIsProduction(false);
$qrUrl = $qrGenerator->generateQr($cuerpoFactura->idFactura, $cuerpoFactura->importeTotal);

return $qrUrl;