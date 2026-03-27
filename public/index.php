<?php

/**
 * Index du projet GSB - Point d'entrée unique de l'application
 * 
 * Ce fichier joue le rôle de ROUTEUR :
 * 1. Il initialise l'environnement (autoload, constantes, session)
 * 2. Il gère l'affichage de l'entête et du pied de page communs
 * 3. Il oriente l'utilisateur vers le bon contrôleur en fonction du paramètre 'uc' dans l'URL
 */

use Modeles\PdoGsb;
use Outils\Utilitaires;

// Chargement automatique des classes (via Composer) et des constantes de configuration
require '../vendor/autoload.php';
require '../config/define.php';

// Démarrage de la session PHP pour conserver l'état de connexion de l'utilisateur
session_start();

// Initialisation de l'accès à la base de données via le modèle PDO singleton
$pdo = PdoGsb::getPdoGsb();

// On vérifie si un utilisateur est déjà connecté
$estConnecte = Utilitaires::estConnecte();

// Affichage du haut de page (logo, menu, styles CSS)
require PATH_VIEWS . 'v_entete.php';

// Récupération du paramètre "Use Case" (uc) qui définit la page demandée
$uc = filter_input(INPUT_GET, 'uc', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Règle de sécurité : si on demande une page mais qu'on n'est pas connecté, 
// on force l'affichage de la page de connexion.
if ($uc && !$estConnecte) {
    $uc = 'connexion';
} elseif (empty($uc)) {
    // Si aucun paramètre n'est fourni, on affiche l'accueil
    $uc = 'accueil';
}

/**
 * AIGUILLAGE (Routing)
 * On inclut le contrôleur PHP correspondant à la demande de l'utilisateur
 */
switch ($uc) {
    case 'connexion':
        include PATH_CTRLS . 'c_connexion.php';
        break;
    case 'accueil':
        include PATH_CTRLS . 'c_accueil.php';
        break;
    case 'gererFrais':
        include PATH_CTRLS . 'c_gererFrais.php';
        break;
    case 'etatFrais':
        include PATH_CTRLS . 'c_etatFrais.php';
        break;
    case 'validerFiche':
        include PATH_CTRLS . 'c_validerFiche.php';
        break;
    case 'suiviPaiement':
        include PATH_CTRLS . 'c_suiviPaiement.php';
        break;
    case 'deconnexion':
        include PATH_CTRLS . 'c_deconnexion.php';
        break;
    default:
        // Si le paramètre uc n'existe pas dans le switch, on affiche une erreur
        Utilitaires::ajouterErreur('La page demandée est introuvable.');
        include PATH_VIEWS . 'v_erreurs.php';
        break;
}

// Affichage du bas de page (scripts JS, copyright)
require PATH_VIEWS . 'v_pied.php';
