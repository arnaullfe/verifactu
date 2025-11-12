<?php

namespace arnaullfe\Verifactu\Services;

use arnaullfe\Verifactu\Models\Respuestas\VerifactuRespuestas;
use arnaullfe\Verifactu\Models\Factura;

class VerifactuClient {
    private bool $isProduction = false;
    private ?string $certificatePath = null;
    private ?string $certificatePassword = null;
    private bool $parseCertificateToPem = false;

    public function setIsProduction(bool $isProduction): void {
        $this->isProduction = $isProduction;
    }

    public function convertPfxToPem(string $pfxPath, string $certificatePassword): string {
        $pemPath = str_replace(['.pfx', '.p12'], '.pem', $pfxPath);
        $command = sprintf(
            'openssl pkcs12 -in %s -out %s -nodes -clcerts -legacy -passin pass:%s',
            escapeshellarg($pfxPath),
            escapeshellarg($pemPath),
            escapeshellarg($certificatePassword)
        );
        exec($command, $output, $status);
        if ($status !== 0) {
            if(file_exists($pemPath)){
              unlink($pemPath);
            }
            throw new \RuntimeException('Error al convertir el certificado PFX a PEM');
        }
        return $pemPath;
    }

    public function setCertificate(string $certificatePath, string $certificatePassword, bool $parseToPem = false): void {
        $this->certificatePassword = $certificatePassword;
        $this->parseCertificateToPem = $parseToPem;
        if ($parseToPem) {
            $this->certificatePath = $this->convertPfxToPem($certificatePath, $certificatePassword);
        } else {
            $this->certificatePath = $certificatePath;
        }
    }

    public function enviarFactura(Factura $factura): array {
        try {
            $errors = $factura->validate();
            if (count($errors) > 0) {
                throw new \InvalidArgumentException(implode("\n", $errors));
            }
            if ($this->certificatePath === null || $this->certificatePassword === null) {
                throw new \InvalidArgumentException('La ruta del certificado y la contraseña son obligatorias');
            }
            $wsdlUrl = $this->getWsdlUrl();

            $options = [
                'local_cert' => $this->certificatePath,
                'passphrase' => $this->certificatePassword,
                'trace' => 1,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ]
                ])
            ];

            $client = new \SoapClient($wsdlUrl, $options);
            $client->__setLocation($this->getUrlEndpoint());
            $result = $client->__soapCall('RegFactuSistemaFacturacion', [$factura->toArray()]);
            $isSuccess = false;
            $message = "Error al enviar la factura";
            if (strtoupper($result->EstadoEnvio) === VerifactuRespuestas::CORRECTO) {
                $isSuccess = true;
                $message = "Factura enviada correctamente";
            } elseif (strtoupper($result->EstadoEnvio) === VerifactuRespuestas::ACEPTADO_CON_ERRORES) {
                $isSuccess = true;
                $message = "Factura enviada con errores";
            }else if(!empty($result->RespuestaLinea) && !empty($result->RespuestaLinea->DescripcionErrorRegistro)){
                $message .= ": " . $result->RespuestaLinea->DescripcionErrorRegistro;
            }
            $this->deleteCertificate();
            return [
                'success' => $isSuccess,
                'message' => $message,
                'data' => $result,
            ];
        } catch (\Throwable $error) {
            $this->deleteCertificate();
            return [
                'success' => false,
                'message' => method_exists($error, 'getMessage') ? $error->getMessage() : 'Error desconocido',
                'data' => $error,
            ];
        }
    }

    private function deleteCertificate(): void {
        if ($this->parseCertificateToPem && $this->certificatePath && file_exists($this->certificatePath)) {
            @unlink($this->certificatePath);
        }
    }

    private function getUrlEndpoint(): string {
        $baseUri = $this->getRequestBaseUri();
        return $baseUri . '/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';
    }

    private function getWsdlUrl(): string {
        $baseUri = $this->getBaseUri();
        return $baseUri . '/static_files/common/internet/dep/aplicaciones/es/aeat/tikeV1.0/cont/ws/SistemaFacturacion.wsdl';
    }

    private function getRequestBaseUri(): string {
        return $this->isProduction ? 'https://www1.agenciatributaria.gob.es' : 'https://prewww1.aeat.es';
    }

    private function getBaseUri(): string {
        return $this->isProduction ? 'https://www1.agenciatributaria.gob.es' : 'https://prewww2.aeat.es';
    }
}
