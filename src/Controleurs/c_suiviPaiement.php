<?php

/**
 * Contrôleur UC "Suivi du paiement".
 *
 * Actions (GET param 'action'):
 * - afficher/lister (défaut): liste des fiches VALIDÉES (VA), filtres par visiteur, stats annuelles
 * - payer (POST): enregistre le paiement d'une fiche (passe RB), avec date/référence si colonnes présentes
 */

use Outils\Utilitaires;
Utilitaires::exigerRole('COMPT');
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!$action) {
    $action = 'afficher';
}

switch ($action) {
    case 'payer':
        $idVisiteur = filter_input(INPUT_POST, 'idVisiteur', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $mois = filter_input(INPUT_POST, 'mois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $datePaiement = filter_input(INPUT_POST, 'datePaiement', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $refPaiement = filter_input(INPUT_POST, 'refPaiement', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $pdo->rembourserFiche($idVisiteur, $mois, $datePaiement, $refPaiement);
        $message = 'Fiche remboursée';
    case 'lister':
    case 'afficher':
    default:
        $filtreVisiteur = filter_input(INPUT_GET, 'visiteur', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $tousVisiteurs = $pdo->getTousVisiteurs();
        if ($filtreVisiteur) {
            $fichesValidees = $pdo->getFichesValidees($filtreVisiteur);
        } else {
            $fichesValidees = $pdo->getFichesValidees();
        }
        $annee = date('Y');
        $statsRemboursements = $pdo->getStatsRemboursements($annee);
        if (isset($message)) {
            include PATH_VIEWS . 'v_messages.php';
        }
        include PATH_VIEWS . 'v_suiviPaiement.php';
        break;
}
