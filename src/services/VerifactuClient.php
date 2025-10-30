<?php

namespace arnaullfe\Verifactu\services;

use arnaullfe\Verifactu\models\VerifactuFactura;

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
            throw new \RuntimeException('Failed to convert PFX to PEM');
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

    public function enviarFactura(VerifactuFactura $factura): array {
        try {
            $errors = $factura->validate();
            if (count($errors) > 0) {
                throw new \InvalidArgumentException(implode("\n", $errors));
            }
            if ($this->certificatePath === null || $this->certificatePassword === null) {
                throw new \InvalidArgumentException('Certificate path and password are required');
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

            $this->deleteCertificate();
            return [
                'success' => true,
                'message' => is_string($result) ? $result : json_encode($result),
                'messageType' => 'success',
            ];
        } catch (\Throwable $error) {
            $this->deleteCertificate();
            return [
                'success' => false,
                'message' => method_exists($error, 'getMessage') ? $error->getMessage() : 'Unknown error',
                'messageType' => 'text',
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
