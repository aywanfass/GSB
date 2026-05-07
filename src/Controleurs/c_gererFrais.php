<?php

/**
 * Contrôleur "Gérer les frais" (UC Visiteur)
 * 
 * Ce fichier permet au visiteur de :
 * - Saisir ses frais forfaitisés (Forfait Étape, Frais Kilométrique, Nuitée, Repas)
 * - Ajouter des nouveaux frais hors forfait (Justifiés par une facture)
 * - Supprimer un frais hors forfait tant que la fiche n'est pas validée
 */

use Outils\Utilitaires;

// On récupère l'identifiant du visiteur connecté via la session
$idVisiteur = $_SESSION['idVisiteur'];

// On calcule le mois actuel au format 'aaaamm' pour les traitements
$mois = Utilitaires::getMois(date('d/m/Y'));
$numAnnee = substr($mois, 0, 4);
$numMois = substr($mois, 4, 2);

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

switch ($action) {
    /**
     * Action : saisirFrais
     * Affiche le formulaire de saisie. Si c'est le premier accès du mois,
     * on crée automatiquement la nouvelle fiche de frais en base de données.
     */
    case 'saisirFrais':
        if ($pdo->estPremierFraisMois($idVisiteur, $mois)) {
            $pdo->creeNouvellesLignesFrais($idVisiteur, $mois);
        }
        break;

    /**
     * Action : validerMajFraisForfait
     * Enregistre les modifications apportées aux quantités forfaitisées (Repas, Nuitée, etc.)
     */
    case 'validerMajFraisForfait':
        $lesFrais = filter_input(INPUT_POST, 'lesFrais', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
        
        // Vérification que les quantités saisies sont bien des nombres
        if (Utilitaires::lesQteFraisValides($lesFrais)) {
            $pdo->majFraisForfait($idVisiteur, $mois, $lesFrais);
        } else {
            Utilitaires::ajouterErreur('Les valeurs des frais doivent être numériques');
            include PATH_VIEWS . 'v_erreurs.php';
        }
        break;

    /**
     * Action : validerCreationFrais
     * Ajoute une nouvelle ligne hors forfait (ex: Parking, Invitation restaurant)
     */
    case 'validerCreationFrais':
        $dateFrais = Utilitaires::dateAnglaisVersFrancais(
            filter_input(INPUT_POST, 'dateFrais', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        );
        $libelle = filter_input(INPUT_POST, 'libelle', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $montant = filter_input(INPUT_POST, 'montant', FILTER_VALIDATE_FLOAT);
        
        // Validation des informations saisies (champs non vides, date valide, etc.)
        Utilitaires::valideInfosFrais($dateFrais, $libelle, $montant);
        
        if (Utilitaires::nbErreurs() != 0) {
            include PATH_VIEWS . 'v_erreurs.php';
        } else {
            // Création effective en base de données
            $pdo->creeNouveauFraisHorsForfait($idVisiteur, $mois, $libelle, $dateFrais, $montant);
        }
        break;

    /**
     * Action : supprimerFrais
     * Supprime définitivement une ligne de frais hors forfait.
     */
    case 'supprimerFrais':
        $idFrais = filter_input(INPUT_GET, 'idFrais', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $pdo->supprimerFraisHorsForfait($idFrais);
        break;
}

// Dans tous les cas, on termine par récupérer les données à jour pour les afficher dans la vue
$lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $mois);
$lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $mois);

// Chargement des vues pour l'affichage du formulaire et de la liste
require PATH_VIEWS . 'v_listeFraisForfait.php';
require PATH_VIEWS . 'v_listeFraisHorsForfait.php';
