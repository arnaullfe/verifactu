<?php
namespace arnaullfe\Verifactu\Models;
/**
 * Enumeración de tipos de operación según la normativa de IVA
 */
enum TipoOperacion: string {
    /** Operación sujeta sin inversión del sujeto pasivo */
    const Subject = 'S1';
    /** Operación sujeta con inversión del sujeto pasivo */
    const PassiveSubject = 'S2';
    /** Operación no sujeta según artículos 7 y 14 */
    const NonSubject = 'N1';
    /** Operación no sujeta por criterios de localización */
    const NonSubjectByLocation = 'N2';
    /** Operación exenta según artículo 20 */
    const ExemptByArticle20 = 'E1';
    /** Operación exenta según artículo 21 */
    const ExemptByArticle21 = 'E2';
    /** Operación exenta según artículo 22 */
    const ExemptByArticle22 = 'E3';
    /** Operación exenta según artículos 23 y 24 */
    const ExemptByArticles23And24 = 'E4';
    /** Operación exenta según artículo 25 */
    const ExemptByArticle25 = 'E5';
    /** Operación exenta por otros motivos */
    const ExemptByOther = 'E6';

    /**
     * Indica si la operación está sujeta a IVA
     * @param TipoOperacion $operacion
     * @return bool
     */
    public static function isSubject(self $operacion): bool {
        return in_array($operacion, [self::Subject, self::PassiveSubject], true);
    }

    /**
     * Indica si la operación no está sujeta a IVA
     * @param TipoOperacion $operacion
     * @return bool
     */
    public static function isNonSubject(self $operacion): bool {
        return in_array($operacion, [self::NonSubject, self::NonSubjectByLocation], true);
    }

    /**
     * Indica si la operación está exenta de IVA
     * @param TipoOperacion $operacion
     * @return bool
     */
    public static function isExempt(self $operacion): bool {
        return in_array($operacion, [
            self::ExemptByArticle20,
            self::ExemptByArticle21,
            self::ExemptByArticle22,
            self::ExemptByArticles23And24,
            self::ExemptByArticle25,
            self::ExemptByOther
        ], true);
    }
}
