<?php

use Outils\Utilitaires;

// Interdire l'accès aux non-comptables
Utilitaires::exigerRole('COMPT');
$idVisiteur = $_SESSION['idVisiteur'];
$mois = Utilitaires::getMois(date('d/m/Y'));
$numAnnee = substr($mois, 0, 4);
$numMois = substr($mois, 4, 2);
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$lesVisiteurs = $pdo->getTousLesVisiteurs();
$lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $mois);
$lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $mois);
switch ($action) {
    case 'validerFiches':
        if ($pdo->estPremierFraisMois($idVisiteur, $mois)) {
            $pdo->creeNouvellesLignesFrais($idVisiteur, $mois);
        }
        require PATH_VIEWS . 'v_listeFraisForfait.php';
        require PATH_VIEWS . 'v_listeFraisHorsForfait.php';
        require PATH_VIEWS . 'v_comptable_validation.php';
        break;

    case 'suivrePaiement':
        include PATH_VIEWS . 'v_comptable_suivre_paiement.php';
        break;

    default:
        include PATH_VIEWS . 'v_acceuil.php';
        break;
}
