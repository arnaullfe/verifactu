<?php
namespace arnaullfe\Verifactu\models\extra;
/**
 * Tipos de operación (VerifactuOperationType)
 */
enum VerifactuOperationType: string {
    /** Operación sujeta y no exenta - Sin inversión del sujeto pasivo */
    const Subject = 'S1';
    /** Operación sujeta y no exenta - Con inversión del sujeto pasivo */
    const PassiveSubject = 'S2';
    /** Operación no sujeta - Artículos 7, 14 y otros */
    const NonSubject = 'N1';
    /** Operación no sujeta por reglas de localización */
    const NonSubjectByLocation = 'N2';
    /** Exenta por el artículo 20 */
    const ExemptByArticle20 = 'E1';
    /** Exenta por el artículo 21 */
    const ExemptByArticle21 = 'E2';
    /** Exenta por el artículo 22 */
    const ExemptByArticle22 = 'E3';
    /** Exenta por los artículos 23 y 24 */
    const ExemptByArticles23And24 = 'E4';
    /** Exenta por el artículo 25 */
    const ExemptByArticle25 = 'E5';
    /** Exenta por otros */
    const ExemptByOther = 'E6';

    /**
     * Check if the operation type is subject.
     * @param VerifactuOperationType $operationType
     * @return bool
     */
    public static function isSubject(self $operationType): bool {
        return in_array($operationType, [self::Subject, self::PassiveSubject], true);
    }

    /**
     * Check if the operation type is non-subject.
     * @param VerifactuOperationType $operationType
     * @return bool
     */
    public static function isNonSubject(self $operationType): bool {
        return in_array($operationType, [self::NonSubject, self::NonSubjectByLocation], true);
    }

    /**
     * Check if the operation type is exempt.
     * @param VerifactuOperationType $operationType
     * @return bool
     */
    public static function isExempt(self $operationType): bool {
        return in_array($operationType, [
            self::ExemptByArticle20,
            self::ExemptByArticle21,
            self::ExemptByArticle22,
            self::ExemptByArticles23And24,
            self::ExemptByArticle25,
            self::ExemptByOther
        ], true);
    }
}
