<?php

namespace arnaullfe\Verifactu\Services;

use arnaullfe\Verifactu\Models\IdFactura;

/**
 * Genera códigos QR para facturas según el protocolo Verifactu
 */
class VerifactuQrGenerator {
    private bool $isProduction = false;
    private bool $isOnlineMode = true;

    public function setIsProduction(bool $isProduction): void {
        $this->isProduction = $isProduction;
    }

    public function setIsOnlineMode(bool $isOnlineMode): void {
        $this->isOnlineMode = $isOnlineMode;
    }

    /**
     * Genera la URL del código QR para una factura
     * @param IdFactura $idFactura Identificador de la factura
     * @param float $totalAmount Importe total de la factura
     * @return string URL del código QR
     */
    public function generateQr(IdFactura $idFactura, float $totalAmount): string {
        $urlBase = $this->isProduction
            ? 'https://www2.agenciatributaria.gob.es'
            : 'https://prewww2.aeat.es';
        $path = $this->isOnlineMode ? 'ValidarQR' : 'ValidarQRNoVerifactu';
        $params = [
            'nif' => $idFactura->idEmisorFactura,
            'numserie' => $idFactura->numSerieFactura,
            'fecha' => $idFactura->fechaExpedicionFactura->format('d-m-Y'),
            'importe' => (string)$totalAmount,
        ];
        $query = http_build_query($params);
        $url = $urlBase . '/wlpl/TIKE-CONT/' . $path . '?' . $query;
        return $url;
    }
}
