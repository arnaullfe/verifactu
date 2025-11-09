# Verifactu PHP Library

Una librería PHP simple y elegante para enviar facturas electrónicas al sistema **VERI*FACTU** de la AEAT. Sin complicaciones, sin dolor de cabeza. ✨

## ¿Qué es esto?

Esta librería te permite generar y enviar facturas electrónicas a la Agencia Tributaria española de forma sencilla. Solo necesitas crear tu factura con los datos básicos, y nosotros nos encargamos del resto: validación, envío SOAP, generación de QR, y todo lo que necesitas para cumplir con la normativa.

## Instalación

```bash
composer require arnaullfe/verifactu
```

**Requisitos:**
- PHP >= 8.1
- Extensión SOAP (`ext-soap`)
- Un certificado digital (PFX/P12) para autenticarte con la AEAT

## Empezar en 5 minutos

Aquí tienes un ejemplo completo de cómo crear y enviar una factura. Es así de simple:

```php
<?php

use arnaullfe\Verifactu\Models\CabeceraFactura;
use arnaullfe\Verifactu\Models\CuerpoFactura;
use arnaullfe\Verifactu\Models\Factura;
use arnaullfe\Verifactu\Models\IdentificacionFiscal;
use arnaullfe\Verifactu\Models\IdFactura;
use arnaullfe\Verifactu\Models\LineaFactura;
use arnaullfe\Verifactu\Models\SistemaInformatico;
use arnaullfe\Verifactu\Models\TipoFactura;
use arnaullfe\Verifactu\Services\VerifactuClient;
use arnaullfe\Verifactu\Services\VerifactuQrGenerator;

// 1. Define tu empresa (emisor)
$EMISOR_NIF = "12345678A";
$EMISOR_NOMBRE = "Mi Empresa SL";
$emisor = new IdentificacionFiscal($EMISOR_NOMBRE, $EMISOR_NIF);

// 2. Crea la estructura de la factura
$cabeceraFactura = new CabeceraFactura($emisor);
$cuerpoFactura = new CuerpoFactura();

// 3. Identifica tu factura (número único, fecha, etc.)
$cuerpoFactura->idFactura = new IdFactura($EMISOR_NIF, "F-2025-2", new DateTime());

// 4. Completa los datos básicos
$cuerpoFactura->nombreRazonEmisor = $EMISOR_NOMBRE;
$cuerpoFactura->tipoFactura = TipoFactura::FACTURA;
$cuerpoFactura->descripcionOperacion = "Venta de productos";
$cuerpoFactura->destinatarios = [
    new IdentificacionFiscal("Cliente SA", "87654321B")
];

// 5. Define los importes (en string con 2 decimales)
$cuerpoFactura->cuotaTotal = "100.00";      // Subtotal sin IVA
$cuerpoFactura->importeTotal = "121.00";    // Total con IVA (100 + 21)

// 6. Información del sistema (tu software de facturación)
$cuerpoFactura->sistemaInformatico = new SistemaInformatico(
    "1",                                    // ID del sistema
    "Mi Sistema de Facturación",            // Nombre
    $EMISOR_NIF,                            // NIF del fabricante
    $EMISOR_NIF,                            // NIF del desarrollador
    "1.0",                                  // Versión
    "1"                                     // Número de instalación
);

// 7. Desglose de impuestos (base, IVA, tipo)
$cuerpoFactura->desglose = [
    new LineaFactura("100.00", "21.00", "21.00")  // Base: 100€, IVA: 21€, Tipo: 21%
];

// 8. Crea la factura completa
$factura = new Factura($cabeceraFactura, $cuerpoFactura);

// 9. Configura el cliente y envía
$client = new VerifactuClient();
$client->setIsProduction(false);  // true para producción
$client->setCertificate("ruta/al/certificado.pfx", "contraseña");

$respuesta = $client->enviarFactura($factura);

// 10. Verifica que todo salió bien
if (empty($respuesta['success'])) {
    throw new Exception("Error al enviar la factura: " . $respuesta['message']);
}

// 11. Genera el código QR para mostrar en tu PDF
$qrGenerator = new VerifactuQrGenerator();
$qrGenerator->setIsProduction(false);
$qrUrl = $qrGenerator->generateQr($cuerpoFactura->idFactura, $cuerpoFactura->importeTotal);

echo "¡Factura enviada! QR: " . $qrUrl;
```

¡Y listo! 🎉 Tu factura ya está registrada en la AEAT.

## Casos Comunes

### Factura Rectificativa

Si necesitas corregir una factura anterior, usa `TipoFactura::R1` y añade el registro anterior:

```php
// Solo si es una factura rectificativa
$cuerpoFactura->tipoFactura = TipoFactura::R1;
$cuerpoFactura->cuotaTotal = "-100.00";      // Negativo para rectificativas
$cuerpoFactura->importeTotal = "-121.00";
$cuerpoFactura->desglose = [
    new LineaFactura("-100.00", "21.00", "21.00")
];

// Necesitas la huella de la factura original (la obtienes al guardar $factura->toArray())
$cuerpoFactura->registroAnterior = new RegistroAnterior(
    $EMISOR_NIF,
    "F-2025-1",                              // Número de la factura original
    new DateTime(),                          // Fecha de la factura original
    "HUELLA_DE_LA_FACTURA_ORIGINAL"         // Hash SHA-256 de la factura original
);
```

### Factura Sin IVA

Para facturas exentas o no sujetas a IVA:

```php
use arnaullfe\Verifactu\Models\TipoImpuesto;
use arnaullfe\Verifactu\Models\TipoRegimen;
use arnaullfe\Verifactu\Models\TipoOperacion;

$cuerpoFactura->cuotaTotal = "100.00";
$cuerpoFactura->importeTotal = "100.00";     // Sin IVA

$cuerpoFactura->desglose = [
    new LineaFactura(
        "100.00",
        "00.00",                              // Sin cuota
        "00.00",                              // Sin tipo impositivo
        TipoImpuesto::IVA,
        TipoRegimen::C01,
        TipoOperacion::NonSubject             // No sujeta
    )
];
```

### Múltiples Líneas de Desglose

Si tienes diferentes tipos de IVA o regímenes:

```php
$cuerpoFactura->desglose = [
    new LineaFactura("500.00", "105.00", "21.00"),  // 21% IVA
    new LineaFactura("300.00", "63.00", "21.00"),   // 21% IVA
    new LineaFactura("200.00", "42.00", "21.00")    // 21% IVA
];

// El cuotaTotal debe ser la suma: 105 + 63 + 42 = 210.00
$cuerpoFactura->cuotaTotal = "210.00";
$cuerpoFactura->importeTotal = "1210.00";  // 1000 (base) + 210 (IVA)
```

## Configuración

### Entorno de Pruebas vs Producción

Por defecto, la librería usa el entorno de pruebas. Cuando estés listo para producción:

```php
$client->setIsProduction(true);
$qrGenerator->setIsProduction(true);
```

### Certificados Digitales

Puedes usar certificados PFX/P12 directamente (se convierten automáticamente):

```php
$client->setCertificate("ruta/al/certificado.pfx", "contraseña");
```

O si ya tienes un certificado PEM:

```php
$client->setCertificate("ruta/al/certificado.pem", "contraseña", false);
```

## Guardar la Huella Digital

Es importante guardar la huella digital de cada factura para poder hacer rectificativas después:

```php
$factura = new Factura($cabeceraFactura, $cuerpoFactura);
$facturaArray = $factura->toArray();

// Guarda $facturaArray en tu base de datos
// La huella está en: $facturaArray['CuerpoFactura']['Huella']
```

Cuando necesites hacer una rectificativa, usa esa huella en `RegistroAnterior`.

## Respuestas del Servicio

El método `enviarFactura()` devuelve un array con esta estructura:

```php
[
    'success' => true,                    // true si todo salió bien
    'message' => 'Mensaje descriptivo',   // Mensaje de la AEAT
    'data' => [...]                       // Respuesta completa del servicio SOAP
]
```

**Estados posibles:**
- ✅ `CORRECTO`: Factura enviada correctamente
- ⚠️ `ACEPTADO_CON_ERRORES`: Aceptada pero con advertencias
- ❌ `ERROR`: Error en el envío

## Validación Automática

La librería valida automáticamente tu factura antes de enviarla. Verifica:

- ✅ Campos obligatorios
- ✅ Formato de NIF (9 caracteres)
- ✅ Cálculo correcto de IVA (con tolerancia de ±0.02€)
- ✅ Coherencia entre tipo de operación e impuestos

Si hay errores, los verás antes de enviar:

```php
$errors = $factura->validate();
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}
```

## Tipos de Factura

```php
TipoFactura::FACTURA      // F1 - Factura ordinaria
TipoFactura::SIMPLIFICADA // F2 - Factura simplificada
TipoFactura::SUSTITUTIVA  // F3 - Factura sustitutiva
TipoFactura::R1           // R1 - Rectificativa (Art 80.1 y 80.2)
TipoFactura::R2           // R2 - Rectificativa (Art. 80.3)
TipoFactura::R3           // R3 - Rectificativa (Art. 80.4)
TipoFactura::R4           // R4 - Rectificativa (Resto)
TipoFactura::R5           // R5 - Rectificativa en simplificadas
```

## Tipos de Impuesto

```php
TipoImpuesto::IVA   // 01 - Impuesto sobre el Valor Añadido
TipoImpuesto::IPSI  // 02 - Impuesto sobre Producción, Servicios e Importación
TipoImpuesto::IGIC  // 03 - Impuesto General Indirecto Canario
TipoImpuesto::OTHER // 05 - Otros
```

## Tipos de Operación

```php
TipoOperacion::Subject           // S1 - Operación sujeta
TipoOperacion::PassiveSubject    // S2 - Operación sujeta con inversión del sujeto pasivo
TipoOperacion::NonSubject        // N1 - Operación no sujeta
TipoOperacion::ExemptByArticle20 // E1 - Operación exenta según artículo 20
// ... y más variantes
```

## Regímenes Especiales

Los más comunes:

```php
TipoRegimen::C01  // Régimen general
TipoRegimen::C02  // Exportación
TipoRegimen::C07  // Criterio de caja
TipoRegimen::C20  // Régimen simplificado
// ... y más (C01 a C20)
```

## Ejemplos Completos

En la carpeta `examples/` encontrarás ejemplos listos para usar:

- `factura.php` - Factura ordinaria con IVA
- `facturaSiRectificativa.php` - Factura rectificativa
- `facturaSinIva.php` - Factura sin IVA (exenta/no sujeta)

## Manejo de Errores

```php
try {
    $result = $client->enviarFactura($factura);
    
    if (!$result['success']) {
        // Algo salió mal
        $errorMessage = $result['message'];
        $errorData = $result['data'];
        
        // Log o maneja el error como necesites
        error_log("Error en factura: " . $errorMessage);
    } else {
        // ¡Todo perfecto!
        echo "Factura enviada: " . $result['message'];
    }
} catch (\Exception $e) {
    // Error de conexión, certificado, etc.
    echo "Error: " . $e->getMessage();
}
```

## Códigos QR

Los códigos QR permiten que tus clientes validen las facturas fácilmente:

```php
$qrGenerator = new VerifactuQrGenerator();
$qrGenerator->setIsProduction(false);
$qrUrl = $qrGenerator->generateQr($cuerpoFactura->idFactura, $cuerpoFactura->importeTotal);

// Usa $qrUrl para generar el código QR en tu PDF
// Ejemplo: <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= $qrUrl ?>" />
```

## Características

- ✅ Validación automática antes del envío
- ✅ Soporte para todos los tipos de factura (ordinarias, simplificadas, rectificativas)
- ✅ Gestión de múltiples impuestos (IVA, IPSI, IGIC)
- ✅ Regímenes especiales de IVA
- ✅ Generación de códigos QR
- ✅ Cálculo automático de huella digital (SHA-256)
- ✅ Entornos de prueba y producción
- ✅ Conversión automática de certificados PFX a PEM

## ¿Necesitas Ayuda?

- 📖 Revisa los ejemplos en `examples/`
- 🐛 Abre un issue en GitHub si encuentras un bug
- 💡 Sugiere mejoras o nuevas funcionalidades

## Licencia

MIT License - Siéntete libre de usar esta librería en tus proyectos.

## Autor

**Arnau Llopart** - [@arnaullfe](https://github.com/arnaullfe)

---

**¿Listo para empezar?** Copia el ejemplo de arriba, ajusta tus datos, y en 5 minutos tendrás tu primera factura enviada. 🚀
