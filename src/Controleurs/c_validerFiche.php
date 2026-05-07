<?php

/**
 * Contrôleur "Valider fiche de frais" (UC Comptable)
 * 
 * Ce fichier gère toutes les actions du comptable lorsqu'il valide une fiche :
 * - Sélection d'un visiteur et d'un mois
 * - Modification des quantités forfaitisées (Repas, Nuitée, etc.)
 * - Refus des frais hors forfait (Préfixage avec REFUSE)
 * - Validation finale avec calcul du montant total
 */

use Outils\Utilitaires;

// Règle de sécurité : Seul un comptable peut accéder à ce contrôleur
Utilitaires::exigerRole('COMPT');

// On récupère l'action à effectuer via l'URL
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!$action) {
    $action = 'afficher'; // Action par défaut
}

switch ($action) {
    /**
     * Action : chargerFiche
     * Déclenchée quand le comptable sélectionne un visiteur et un mois.
     * On récupère toutes les données (forfait, hors forfait) pour les afficher.
     */
    case 'chargerFiche':
        // Récupération des paramètres envoyés par le formulaire
        $idVisiteur = filter_input(INPUT_POST, 'visiteur', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $mois = filter_input(INPUT_POST, 'mois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // On récupère la liste des mois pour lesquels ce visiteur a des fiches
        $lesMois = $pdo->getLesMoisDisponibles($idVisiteur);
        
        // Si aucun mois n'est sélectionné, on prend le premier de la liste
        if (!$mois && count($lesMois) > 0) {
            $mois = $lesMois[0]['mois'];
        }
        
        // On charge les données nécessaires à la vue
        $tousVisiteurs = $pdo->getTousVisiteurs();
        $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $mois);
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $mois);
        
        // Calcul automatique du montant total (incluant la règle KM de 2025)
        $montantCalcule = $pdo->calculerMontantFiche($idVisiteur, $mois);
        
        // Affichage de la vue de validation
        include PATH_VIEWS . 'v_validerFiche.php';
        break;

    /**
     * Action : refuserHF
     * Déclenchée quand le comptable clique sur "Refuser" pour un frais hors forfait.
     * Le libellé du frais sera modifié en base de données pour commencer par "REFUSE ".
     */
    case 'refuserHF':
        $idFrais = filter_input(INPUT_GET, 'idFrais', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $idVisiteur = filter_input(INPUT_GET, 'visiteur', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $mois = filter_input(INPUT_GET, 'mois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // Appel au modèle pour modifier le libellé
        $pdo->refuserFraisHorsForfait($idFrais);
        
        // Après modification, on recharge les données pour mettre à jour l'affichage (grisage)
        $tousVisiteurs = $pdo->getTousVisiteurs();
        $lesMois = $pdo->getLesMoisDisponibles($idVisiteur);
        $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $mois);
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $mois);
        $montantCalcule = $pdo->calculerMontantFiche($idVisiteur, $mois);
        
        include PATH_VIEWS . 'v_validerFiche.php';
        break;

    /**
     * Action : valider
     * Déclenchée quand le comptable clique sur "Valider" en bas de page.
     * Enregistre les modifications de quantités et passe la fiche à l'état "Validée" (VA).
     */
    case 'valider':
        $idVisiteur = filter_input(INPUT_POST, 'visiteur', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $mois = filter_input(INPUT_POST, 'mois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // On récupère le tableau des quantités forfaitisées (Repas, Nuitée, etc.)
        $lesFrais = filter_input(INPUT_POST, 'lesFrais', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
        
        // Si les données sont valides, on met à jour la base de données
        if ($lesFrais && Utilitaires::lesQteFraisValides($lesFrais)) {
            $pdo->majFraisForfait($idVisiteur, $mois, $lesFrais);
        }
        
        // Calcul final du montant de la fiche
        $montant = $pdo->calculerMontantFiche($idVisiteur, $mois);
        
        // Mise à jour du montant total et du statut de la fiche (passage à VA)
        $pdo->setMontantValide($idVisiteur, $mois, $montant);
        $pdo->majEtatFicheFrais($idVisiteur, $mois, 'VA');
        
        $message = 'La fiche de frais a bien été validée.';
        
        // On recharge les données pour l'affichage final
        $tousVisiteurs = $pdo->getTousVisiteurs();
        $lesMois = $pdo->getLesMoisDisponibles($idVisiteur);
        $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $mois);
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $mois);
        $montantCalcule = $montant;
        
        include PATH_VIEWS . 'v_messages.php';
        include PATH_VIEWS . 'v_validerFiche.php';
        break;

    /**
     * Action par défaut : afficher le sélecteur
     */
    case 'afficher':
    default:
        $tousVisiteurs = $pdo->getTousVisiteurs();
        include PATH_VIEWS . 'v_validerFiche.php';
        break;
}
