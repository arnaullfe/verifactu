<?php

namespace arnaullfe\Verifactu\services;

use arnaullfe\Verifactu\models\parts\cuerpo\VerifactuCuerpoIdFactura;

class VerifactuQrGenerator {
    private bool $isProduction = false;
    private bool $isOnlineMode = true;

    public function setIsProduction(bool $isProduction): void {
        $this->isProduction = $isProduction;
    }

    public function setIsOnlineMode(bool $isOnlineMode): void {
        $this->isOnlineMode = $isOnlineMode;
    }

    public function generateQr(VerifactuCuerpoIdFactura $idFactura, float $totalAmount): string {
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
