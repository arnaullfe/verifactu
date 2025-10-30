<?php

namespace arnaullfe\Verifactu\models\parts\cuerpo;
/**
 * Sistema informático de facturación
 * @field SistemaInformatico
 */
class VerifactuCuerpoSistemaInformatico {
    public string $vendorName;
    public string $vendorNif;
    public string $name;
    public string $id;
    public string $version;
    public string $installationNumber;
    public bool $onlySupportsVerifactu = true;
    public bool $supportsMultipleTaxpayers = false;
    public bool $hasMultipleTaxpayers = false;

    public function __construct($id,$name,$vendorName,$vendorNif,$version,$installationNumber,$onlySupportsVerifactu = true,$supportsMultipleTaxpayers = false,$hasMultipleTaxpayers = false) {
        $this->id = $id;
        $this->vendorName = $vendorName;
        $this->vendorNif = $vendorNif;
        $this->name = $name;
        $this->version = $version;
        $this->installationNumber = $installationNumber;
        $this->onlySupportsVerifactu = $onlySupportsVerifactu;
        $this->supportsMultipleTaxpayers = $supportsMultipleTaxpayers;
        $this->hasMultipleTaxpayers = $hasMultipleTaxpayers;
    }

    public function toArray(): array {
        return [
            'NombreRazon' => $this->vendorName,
            'NIF' => $this->vendorNif,
            'NombreSistemaInformatico' => $this->name,
            'IdSistemaInformatico' => !empty($this->id) ? $this->id : null,
            'Version' => !empty($this->version) ? $this->version : null,
            'NumeroInstalacion' => !empty($this->installationNumber) ? $this->installationNumber : null,
            'TipoUsoPosibleSoloVerifactu' => $this->onlySupportsVerifactu ? 'S' : 'N',
            'TipoUsoPosibleMultiOT' => $this->supportsMultipleTaxpayers ? 'S' : 'N',
            'IndicadorMultiplesOT' => $this->hasMultipleTaxpayers ? 'S' : 'N'
        ];
    }
}
