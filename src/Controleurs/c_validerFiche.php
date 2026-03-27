<?php

/**
 * Contrôleur UC "Valider fiche de frais".
 *
 * Actions (GET param 'action'):
 * - afficher (défaut): affiche le sélecteur visiteur/mois
 * - chargerFiche (POST): charge frais forfait/hors-forfait pour un visiteur/mois
 * - refuserHF (GET): marque une ligne hors-forfait comme REFUSE
 * - valider (POST): met à jour les quantités, calcule et enregistre le montant validé, passe la fiche en VA
 */

use Outils\Utilitaires;
Utilitaires::exigerRole('COMPT');
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!$action) {
    $action = 'afficher';
}

switch ($action) {
    case 'chargerFiche':
        $idVisiteur = filter_input(INPUT_POST, 'visiteur', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $lesMois = $pdo->getLesMoisDisponibles($idVisiteur);
        $mois = filter_input(INPUT_POST, 'mois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$mois && count($lesMois) > 0) {
            $mois = $lesMois[0]['mois'];
        }
        $tousVisiteurs = $pdo->getTousVisiteurs();
        $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $mois);
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $mois);
        $montantCalcule = $pdo->calculerMontantFiche($idVisiteur, $mois);
        include PATH_VIEWS . 'v_validerFiche.php';
        break;
    case 'refuserHF':
        $idFrais = filter_input(INPUT_GET, 'idFrais', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $idVisiteur = filter_input(INPUT_GET, 'visiteur', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $mois = filter_input(INPUT_GET, 'mois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $pdo->refuserFraisHorsForfait($idFrais);
        $tousVisiteurs = $pdo->getTousVisiteurs();
        $lesMois = $pdo->getLesMoisDisponibles($idVisiteur);
        $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $mois);
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $mois);
        $montantCalcule = $pdo->calculerMontantFiche($idVisiteur, $mois);
        include PATH_VIEWS . 'v_validerFiche.php';
        break;
    case 'valider':
        $idVisiteur = filter_input(INPUT_POST, 'visiteur', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $mois = filter_input(INPUT_POST, 'mois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $lesFrais = filter_input(INPUT_POST, 'lesFrais', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
        if ($lesFrais && Utilitaires::lesQteFraisValides($lesFrais)) {
            $pdo->majFraisForfait($idVisiteur, $mois, $lesFrais);
        }
        $montant = $pdo->calculerMontantFiche($idVisiteur, $mois);
        $pdo->setMontantValide($idVisiteur, $mois, $montant);
        $pdo->majEtatFicheFrais($idVisiteur, $mois, ETAT_VA);
        $message = 'Fiche validée';
        $tousVisiteurs = $pdo->getTousVisiteurs();
        $lesMois = $pdo->getLesMoisDisponibles($idVisiteur);
        $lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $mois);
        $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $mois);
        $montantCalcule = $montant;
        include PATH_VIEWS . 'v_messages.php';
        include PATH_VIEWS . 'v_validerFiche.php';
        break;
    case 'afficher':
    default:
        $tousVisiteurs = $pdo->getTousVisiteurs();
        include PATH_VIEWS . 'v_validerFiche.php';
        break;
}
