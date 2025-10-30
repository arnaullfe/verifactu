<?php
namespace arnaullfe\Verifactu\Models\Respuestas;

/**
 * Enumeración con los posibles estados de respuesta del sistema Verifactu
 */
enum VerifactuRespuestas {
    const CORRECTO = 'CORRECTO';
    const ACEPTADO_CON_ERRORES = 'ACEPTADOCONERRORES';
    const INCORRECTO = 'INCORRECTO';
}