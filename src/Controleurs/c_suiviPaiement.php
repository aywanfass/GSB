<?php

/**
 * Contrôleur "Suivi du paiement" (UC Comptable)
 * 
 * Ce fichier permet au comptable de :
 * - Lister les fiches qui ont été validées (État VA)
 * - Filtrer ces fiches par visiteur
 * - Enregistrer le paiement effectif d'une fiche (passage à l'état RB : Remboursé)
 * - Consulter les statistiques annuelles de remboursement
 */

use Outils\Utilitaires;

// Sécurité : Vérification que l'utilisateur est bien un comptable
Utilitaires::exigerRole('COMPT');

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!$action) {
    $action = 'afficher';
}

switch ($action) {
    /**
     * Action : payer
     * Déclenchée quand le comptable clique sur le bouton "Payer" dans la liste.
     * On change l'état de la fiche de VA (Validée) à RB (Remboursée).
     */
    case 'payer':
        $idVisiteur = filter_input(INPUT_POST, 'idVisiteur', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $mois = filter_input(INPUT_POST, 'mois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // On pourrait ici récupérer une date de paiement ou une référence
        $datePaiement = filter_input(INPUT_POST, 'datePaiement', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $refPaiement = filter_input(INPUT_POST, 'refPaiement', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // Appel au modèle pour enregistrer le remboursement
        $pdo->rembourserFiche($idVisiteur, $mois, $datePaiement, $refPaiement);
        
        $message = 'La fiche a bien été marquée comme remboursée.';
        // On laisse couler vers l'affichage de la liste
        
    /**
     * Action : lister / afficher
     * Affiche la liste des fiches en attente de paiement, éventuellement filtrées par visiteur.
     */
    case 'lister':
    case 'afficher':
    default:
        // Gestion du filtre par visiteur
        $filtreVisiteur = filter_input(INPUT_GET, 'visiteur', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $tousVisiteurs = $pdo->getTousVisiteurs();
        
        if ($filtreVisiteur) {
            // Liste filtrée
            $fichesValidees = $pdo->getFichesValidees($filtreVisiteur);
        } else {
            // Liste complète des fiches validées
            $fichesValidees = $pdo->getFichesValidees();
        }
        
        // Récupération des statistiques pour le graphique ou le tableau récapitulatif
        $anneeCourante = date('Y');
        $statsRemboursements = $pdo->getStatsRemboursements($anneeCourante);
        
        // Inclusion des messages d'alerte s'il y en a
        if (isset($message)) {
            include PATH_VIEWS . 'v_messages.php';
        }
        
        // Affichage de la vue de suivi des paiements
        include PATH_VIEWS . 'v_suiviPaiement.php';
        break;
}
