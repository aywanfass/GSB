<?php

namespace App\Services;

/**
 * Service de calcul des indemnités kilométriques.
 *
 * - Lit sa configuration depuis config/indemnites_km.php.
 * - Peut être désactivé (fallback au prix unitaire BDD pour 'KM').
 * - Calcule un montant en fonction d'une plage de puissance et de tranches de kilomètres.
 */
class IndemniteKmService
{
    /**
     * Charge la configuration du barème kilométrique.
     *
     * @return array Tableau associatif [enabled, default_puissance_cv, bareme]
     */
    public static function getConfig(): array
    {
        $path = __DIR__ . '/../../config/indemnites_km.php';
        if (is_file($path)) {
            $cfg = include $path;
            if (is_array($cfg)) {
                return $cfg;
            }
        }
        return [
            'enabled' => false,
            'default_puissance_cv' => null,
            'bareme' => []
        ];
    }

    /**
     * Indique si le barème est activé.
     *
     * @return bool True si activé, False sinon
     */
    public static function isEnabled(): bool
    {
        $c = self::getConfig();
        return !empty($c['enabled']);
    }

    /**
     * Retourne la puissance fiscale par défaut (si définie).
     *
     * @return int|null Puissance en CV ou null si absente
     */
    public static function getDefaultPuissance(): ?int
    {
        $c = self::getConfig();
        if (array_key_exists('default_puissance_cv', $c)) {
            $v = $c['default_puissance_cv'];
            if (is_int($v)) {
                return $v;
            }
            if (is_string($v) && ctype_digit($v)) {
                return (int)$v;
            }
        }
        return null;
    }

    /**
     * Calcule le montant de l'indemnité kilométrique.
     *
     * Règle de décision:
     * - Si le barème est vide/non défini: fallback au prix unitaire (BDD) * kilomètres.
     * - Sinon: on sélectionne la plage de puissance, puis la première tranche km satisfaisante,
     *   et on applique montant = fixe + coef * kilomètres.
     *
     * @param int        $kilometres    Distance parcourue en km (>=0)
     * @param int        $puissanceCv   Puissance fiscale du véhicule
     * @param float      $fallbackUnit  Prix unitaire fallback (valeur BDD pour 'KM')
     * @param array|null $config        Config alternative (tests), sinon lecture depuis fichier
     * @return float                    Montant calculé
     */
    public static function computeMontant(int $kilometres, int $puissanceCv, float $fallbackUnit, ?array $config = null): float
    {
        $c = $config ?? self::getConfig();
        $bareme = $c['bareme'] ?? [];
        if ($kilometres <= 0) {
            return 0.0;
        }
        if (!$bareme) {
            return $kilometres * $fallbackUnit;
        }
        foreach ($bareme as $range) {
            $pmin = $range['puissance_min'] ?? null;
            $pmax = $range['puissance_max'] ?? null;
            $inRange = ($pmin === null || $puissanceCv >= (int)$pmin)
                && ($pmax === null || $puissanceCv <= (int)$pmax);
            if ($inRange) {
                $tranches = $range['tranches'] ?? [];
                foreach ($tranches as $tr) {
                    $kmMax = $tr['km_max'] ?? null;
                    if ($kmMax === null || $kilometres <= (int)$kmMax) {
                        $coef = isset($tr['coef']) ? (float)$tr['coef'] : 0.0;
                        $fixe = isset($tr['fixe']) ? (float)$tr['fixe'] : 0.0;
                        $val = $fixe + $coef * $kilometres;
                        return $val > 0 ? $val : 0.0;
                    }
                }
                break;
            }
        }
        return $kilometres * $fallbackUnit;
    }
}