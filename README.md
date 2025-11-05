# Verifactu PHP Library

Librería PHP para la implementación del sistema **VERI*FACTU** para la facturación electrónica en España, gestionada por la Agencia Estatal de Administración Tributaria (AEAT).

## Descripción

Esta librería proporciona una interfaz completa para generar y enviar facturas electrónicas al sistema VERI*FACTU de la AEAT. Permite crear facturas con toda la información requerida, validarlas y enviarlas mediante SOAP utilizando certificados digitales.

## Requisitos

- PHP >= 8.1
- Extensión SOAP (`ext-soap`)
- Certificado digital (PFX/P12) para autenticación con la AEAT
- OpenSSL (para conversión de certificados si se requiere)

## Instalación

```bash
composer require arnaullfe/verifactu
```

## Características Principales

- ✅ Validación completa de facturas antes del envío
- ✅ Soporte para facturas ordinarias, simplificadas y rectificativas
- ✅ Gestión de múltiples tipos de impuestos (IVA, IPSI, IGIC)
- ✅ Soporte para regímenes especiales de IVA
- ✅ Generación de códigos QR para validación de facturas
- ✅ Cálculo automático de la huella digital (SHA-256)
- ✅ Soporte para entornos de prueba y producción
- ✅ Conversión automática de certificados PFX a PEM

## Estructura de la Librería

### Modelos (`Models/`)

La librería utiliza un modelo orientado a objetos para representar todos los elementos de una factura:

#### Factura Principal
- **`Factura`**: Contenedor principal que agrupa la cabecera y el cuerpo de la factura
- **`CabeceraFactura`**: Información del emisor y representante
- **`CuerpoFactura`**: Contenido principal de la factura con todos los detalles

#### Identificación
- **`IdFactura`**: Identificador único de la factura (NIF emisor, número de serie, fecha)
- **`IdentificacionFiscal`**: Identificación fiscal española (NIF)
- **`IdentificacionFiscalExtranjera`**: Identificación fiscal para entidades extranjeras

#### Impuestos y Operaciones
- **`LineaFactura`**: Línea de desglose de impuestos con validación automática
- **`TipoImpuesto`**: Enum con tipos de impuestos (IVA, IPSI, IGIC)
- **`TipoRegimen`**: Enum con regímenes especiales de IVA
- **`TipoOperacion`**: Enum con tipos de operación (sujeta, exenta, no sujeta)

#### Otros
- **`TipoFactura`**: Enum con tipos de factura (ordinaria, simplificada, rectificativa)
- **`SistemaInformatico`**: Información del sistema que genera la factura
- **`RegistroAnterior`**: Referencia a facturas anteriores (para encadenamiento)

### Servicios (`Services/`)

- **`VerifactuClient`**: Cliente SOAP para enviar facturas a la AEAT
- **`VerifactuQrGenerator`**: Generador de códigos QR para validación

## Uso Básico

### 1. Configurar el Cliente

```php
use arnaullfe\Verifactu\Services\VerifactuClient;

$client = new VerifactuClient();
$client->setIsProduction(false); // true para producción
$client->setCertificate('/ruta/al/certificado.pfx', 'contraseña', true); // true para convertir a PEM
```

### 2. Crear una Factura

```php
use arnaullfe\Verifactu\Models\*;
use arnaullfe\Verifactu\Models\TipoFactura;
use arnaullfe\Verifactu\Models\TipoImpuesto;
use arnaullfe\Verifactu\Models\TipoRegimen;
use arnaullfe\Verifactu\Models\TipoOperacion;

// Identificación del emisor
$emisor = new IdentificacionFiscal('Mi Empresa SL', '12345678A');

// Cabecera de la factura
$cabecera = new CabeceraFactura($emisor);

// Identificador de la factura
$idFactura = new IdFactura(
    '12345678A',                    // NIF del emisor
    'FAC-2024-001',                 // Número de serie
    new DateTime('2024-01-15')      // Fecha de expedición
);

// Destinatario
$destinatario = new IdentificacionFiscal('Cliente SA', '87654321B');

// Sistema informático
$sistemaInformatico = new SistemaInformatico(
    'SISTEMA-001',                  // ID del sistema
    'Mi Sistema de Facturación',    // Nombre
    'Desarrollador SL',             // Fabricante
    '11111111A',                    // NIF del fabricante
    '1.0',                          // Versión
    'INST-001'                      // Número de instalación
);

// Líneas de desglose de impuestos
$lineaFactura = new LineaFactura(
    '1000.00',                      // Base imponible
    '210.00',                       // Cuota repercutida
    '21.00',                        // Tipo impositivo (21%)
    TipoImpuesto::IVA,              // Tipo de impuesto
    TipoRegimen::C01,               // Régimen general
    TipoOperacion::Subject           // Operación sujeta
);

// Cuerpo de la factura
$cuerpo = new CuerpoFactura();
$cuerpo->idFactura = $idFactura;
$cuerpo->nombreRazonEmisor = 'Mi Empresa SL';
$cuerpo->tipoFactura = TipoFactura::FACTURA;
$cuerpo->descripcionOperacion = 'Venta de productos';
$cuerpo->destinatarios = [$destinatario];
$cuerpo->cuotaTotal = 210.00;
$cuerpo->importeTotal = 1210.00;
$cuerpo->sistemaInformatico = $sistemaInformatico;
$cuerpo->desglose = [$lineaFactura];

// Factura completa
$factura = new Factura($cabecera, $cuerpo);
```

### 3. Validar la Factura

```php
$errors = $factura->validate();

if (count($errors) > 0) {
    echo "Errores de validación:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
} else {
    echo "Factura válida\n";
}
```

### 4. Enviar la Factura

```php
$result = $client->enviarFactura($factura);

if ($result['success']) {
    echo "Factura enviada: " . $result['message'] . "\n";
    // Acceder a la respuesta completa: $result['data']
} else {
    echo "Error: " . $result['message'] . "\n";
}
```

### 5. Generar Código QR

```php
use arnaullfe\Verifactu\Services\VerifactuQrGenerator;

$qrGenerator = new VerifactuQrGenerator();
$qrGenerator->setIsProduction(false);
$qrGenerator->setIsOnlineMode(true);

$qrUrl = $qrGenerator->generateQr($idFactura, 1210.00);
echo "URL del QR: $qrUrl\n";
```

## Ejemplos Avanzados

### Factura Rectificativa

```php
// Para facturas rectificativas, necesitas el registro anterior
$registroAnterior = new RegistroAnterior(
    '12345678A',
    'FAC-2024-001',
    new DateTime('2024-01-15'),
    'HUELLA_DE_LA_FACTURA_ORIGINAL'
);

$cuerpo->registroAnterior = $registroAnterior;
$cuerpo->tipoFactura = TipoFactura::R1; // Tipo rectificativa
```

### Múltiples Líneas de Desglose

```php
$linea1 = new LineaFactura('500.00', '105.00', '21.00', TipoImpuesto::IVA, TipoRegimen::C01, TipoOperacion::Subject);
$linea2 = new LineaFactura('300.00', '0.00', null, TipoImpuesto::IVA, TipoRegimen::C02, TipoOperacion::ExemptByArticle20);

$cuerpo->desglose = [$linea1, $linea2];
```

### Operaciones Exentas o No Sujetas

```php
// Operación exenta
$lineaExenta = new LineaFactura(
    '1000.00',
    null,                           // Sin cuota para exentas
    null,                           // Sin tipo impositivo para exentas
    TipoImpuesto::IVA,
    TipoRegimen::C01,
    TipoOperacion::ExemptByArticle20
);

// Operación no sujeta
$lineaNoSujeta = new LineaFactura(
    '1000.00',
    null,
    null,
    TipoImpuesto::IVA,
    TipoRegimen::C01,
    TipoOperacion::NonSubject
);
```

## Validación

La librería incluye validación automática que verifica:

- ✅ Campos obligatorios
- ✅ Longitud de campos
- ✅ Formato de NIF (9 caracteres)
- ✅ Coherencia entre tipo de operación y campos de impuestos
- ✅ Cálculo correcto de cuotas (con tolerancia de ±0.02€)
- ✅ Estructura completa de la factura

### Ejemplo de Validación

```php
$errors = $factura->validate();

if (empty($errors)) {
    // Proceed with sending
} else {
    // Handle errors
    foreach ($errors as $error) {
        // Log or display error
    }
}
```

## Tipos y Enumeraciones

### TipoFactura
- `FACTURA` (F1): Factura ordinaria
- `SIMPLIFICADA` (F2): Factura simplificada
- `SUSTITUTIVA` (F3): Factura sustitutiva
- `R1` a `R5`: Facturas rectificativas

### TipoImpuesto
- `IVA` (01): Impuesto sobre el Valor Añadido
- `IPSI` (02): Impuesto sobre Producción, Servicios e Importación
- `IGIC` (03): Impuesto General Indirecto Canario
- `OTHER` (05): Otros

### TipoRegimen
Incluye más de 20 regímenes especiales (C01 a C20), como:
- `C01`: Régimen general
- `C02`: Exportación
- `C07`: Criterio de caja
- `C20`: Régimen simplificado
- Y muchos más...

### TipoOperacion
- `Subject` (S1): Operación sujeta sin inversión del sujeto pasivo
- `PassiveSubject` (S2): Operación sujeta con inversión del sujeto pasivo
- `NonSubject` (N1): Operación no sujeta
- `ExemptByArticle20` (E1): Operación exenta según artículo 20
- Y otras variantes...

## Entornos

### Entorno de Pruebas (por defecto)

```php
$client->setIsProduction(false);
```

Utiliza los servidores de preproducción de la AEAT:
- WSDL: `https://prewww2.aeat.es/...`
- Endpoint: `https://prewww1.aeat.es/...`

### Entorno de Producción

```php
$client->setIsProduction(true);
```

Utiliza los servidores de producción de la AEAT:
- WSDL: `https://www1.agenciatributaria.gob.es/...`
- Endpoint: `https://www1.agenciatributaria.gob.es/...`

## Certificados Digitales

### Opción 1: Certificado PEM (recomendado)

```php
$client->setCertificate('/ruta/certificado.pem', 'contraseña', false);
```

### Opción 2: Certificado PFX/P12 (conversión automática)

```php
$client->setCertificate('/ruta/certificado.pfx', 'contraseña', true);
// Se convierte automáticamente a PEM y se elimina después del uso
```

### Conversión Manual

```php
$pemPath = $client->convertPfxToPem('/ruta/certificado.pfx', 'contraseña');
```

## Respuestas del Servicio

El método `enviarFactura()` devuelve un array con:

```php
[
    'success' => true/false,
    'message' => 'Mensaje descriptivo',
    'data' => // Objeto con la respuesta completa del servicio SOAP
]
```

### Estados de Respuesta

- **CORRECTO**: Factura enviada correctamente
- **ACEPTADO_CON_ERRORES**: Factura aceptada pero con advertencias
- **ERROR**: Error en el envío

## Códigos QR

Los códigos QR permiten la validación de facturas por parte de los clientes:

```php
$qrGenerator = new VerifactuQrGenerator();
$qrGenerator->setIsProduction(false);
$qrGenerator->setIsOnlineMode(true); // true para modo online, false para modo offline

$qrUrl = $qrGenerator->generateQr($idFactura, $importeTotal);
```

### Modos de QR

- **Online** (`ValidarQR`): Requiere conexión a internet para validar
- **Offline** (`ValidarQRNoVerifactu`): Validación sin conexión

## Huella Digital

La huella digital se calcula automáticamente usando SHA-256 y se incluye en el envío. Se calcula a partir de:

- ID del emisor
- Número de serie
- Fecha de expedición
- Tipo de factura
- Cuota total
- Importe total
- Huella del registro anterior (si existe)
- Fecha y hora de generación

## Manejo de Errores

```php
try {
    $result = $client->enviarFactura($factura);
    
    if (!$result['success']) {
        // Manejar error
        $errorMessage = $result['message'];
        $errorData = $result['data'];
    }
} catch (\Exception $e) {
    // Manejar excepciones
    echo "Error: " . $e->getMessage();
}
```

## Estructura de Archivos

```
src/
├── Models/
│   ├── CabeceraFactura.php
│   ├── CuerpoFactura.php
│   ├── Factura.php
│   ├── IdentificacionFiscal.php
│   ├── IdentificacionFiscalExtranjera.php
│   ├── IdFactura.php
│   ├── LineaFactura.php
│   ├── RegistroAnterior.php
│   ├── Respuestas/
│   │   └── VerifactuRespuestas.php
│   ├── SistemaInformatico.php
│   ├── TipoFactura.php
│   ├── TipoImpuesto.php
│   ├── TipoOperacion.php
│   └── TipoRegimen.php
└── Services/
    ├── VerifactuClient.php
    └── VerifactuQrGenerator.php
```

## Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo LICENSE para más detalles.

## Autor

**Arnau Llopart**

- GitHub: [@arnaullfe](https://github.com/arnaullfe)

## Soporte

Para reportar problemas o solicitar funcionalidades, por favor abre un issue en el repositorio de GitHub.

## Referencias

- [AEAT - VERI*FACTU](https://www.agenciatributaria.gob.es/)
- Documentación oficial del sistema VERI*FACTU de la AEAT

