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
     * Lit le fichier de configuration des indemnités kilométriques.
     * Le fichier se trouve dans le dossier 'config' à la racine.
     *
     * @return array Tableau contenant la configuration complète
     */
    public static function getConfig(): array
    {
        // Chemin vers le fichier de config (2 niveaux au dessus de ce fichier)
        $cheminFichier = __DIR__ . '/../../config/indemnites_km.php';

        // On vérifie si le fichier existe bien sur le disque
        if (file_exists($cheminFichier)) {
            $config = include $cheminFichier;
            // On s'assure que le fichier retourne bien un tableau
            if (is_array($config)) {
                return $config;
            }
        }

        // Si le fichier est absent ou invalide, on retourne une config par défaut (désactivé)
        return [
            'enabled' => false,
            'default_puissance_cv' => '4CV_D',
            'bareme' => []
        ];
    }

    /**
     * Permet de savoir si le barème KM est activé dans la config.
     *
     * @return bool Vrai si activé, faux sinon
     */
    public static function isEnabled(): bool
    {
        $config = self::getConfig();
        // On vérifie la présence de la clé 'enabled' et sa valeur
        if (isset($config['enabled']) && $config['enabled'] == true) {
            return true;
        }
        return false;
    }

    /**
     * Récupère la puissance fiscale par défaut configurée.
     * Utilisée quand un visiteur n'a pas de puissance définie en BDD.
     *
     * @return string La clé de puissance (ex: '4CV_D')
     */
    public static function getDefaultPuissance(): string
    {
        $config = self::getConfig();
        if (isset($config['default_puissance_cv'])) {
            return (string) $config['default_puissance_cv'];
        }
        return '4CV_D';
    }

    /**
     * Fonction principale de calcul du montant des frais kilométriques.
     *
     * @param int    $kilometres   Nombre de kilomètres saisis
     * @param string $puissanceKey Puissance du véhicule (ex: '5/6CV_D')
     * @param float  $tarifBDD     Tarif par défaut de la table fraisforfait (utilisé en cas d'erreur)
     * @return float               Le montant calculé arrondi à 2 décimales
     */
    public static function computeMontant(int $kilometres, string $puissanceKey, float $tarifBDD): float
    {
        $config = self::getConfig();
        $bareme = [];
        if (isset($config['bareme'])) {
            $bareme = $config['bareme'];
        }

        // Si 0 km, le montant est forcément 0
        if ($kilometres <= 0) {
            return 0.0;
        }

        // On cherche d'abord avec la clé exacte (ex: '5/6CV_D')
        if (isset($bareme[$puissanceKey])) {
            $infosTarif = $bareme[$puissanceKey];
            if (isset($infosTarif['tarif'])) {
                $montant = $kilometres * (float) $infosTarif['tarif'];
                return (float) number_format($montant, 2, '.', '');
            }
        }

        // Sinon, on essaie une clé simplifiée (ex: '56CV_D') pour être plus robuste
        $cleSimplifiee = str_replace('/', '', $puissanceKey);
        if (isset($bareme[$cleSimplifiee])) {
            $infosTarif = $bareme[$cleSimplifiee];
            if (isset($infosTarif['tarif'])) {
                $montant = $kilometres * (float) $infosTarif['tarif'];
                return (float) number_format($montant, 2, '.', '');
            }
        }

        // Enfin, si rien n'est trouvé, on utilise le tarif par défaut passé en paramètre
        $montantDefaut = $kilometres * $tarifBDD;
        return (float) number_format($montantDefaut, 2, '.', '');
    }
}