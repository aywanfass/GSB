<?php

/**
 * Contrôleur "Connexion"
 * 
 * Ce fichier gère l'authentification des utilisateurs (Visiteurs et Comptables) :
 * - Affichage du formulaire de connexion
 * - Vérification du login et du mot de passe
 * - Initialisation de la session en cas de succès
 * - Gestion des messages d'erreur en cas d'échec
 */

use Outils\Utilitaires;

// On récupère l'action à effectuer (demander la connexion ou valider le formulaire)
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!$action) {
    $action = 'demandeConnexion'; // Action par défaut
}

switch ($action) {
    /**
     * Action : demandeConnexion
     * Affiche simplement la page avec les champs Login et Mot de passe.
     */
    case 'demandeConnexion':
        include PATH_VIEWS . 'v_connexion.php';
        break;

    /**
     * Action : valideConnexion
     * Déclenchée quand l'utilisateur clique sur "Se connecter".
     */
    case 'valideConnexion':
        $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $mdp = filter_input(INPUT_POST, 'mdp', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // 1. On récupère d'abord les informations de l'utilisateur associé à ce login
        $visiteur = $pdo->getInfosVisiteur($login);
        
        // 2. On vérifie si l'utilisateur existe et si le mot de passe correspond
        // On utilise password_verify pour comparer le mot de passe saisi avec le hash stocké en BDD
        $hashBdd = $pdo->getMdpVisiteur($login);
        
        if (!$visiteur || !password_verify($mdp, $hashBdd)) {
            // Si utilisateur inconnu ou mdp incorrect -> Erreur
            Utilitaires::ajouterErreur('Login ou mot de passe incorrect.');
            include PATH_VIEWS . 'v_erreurs.php';
            include PATH_VIEWS . 'v_connexion.php';
        } else {
            // 3. Authentification réussie
            $id = $visiteur['id'];
            $nom = $visiteur['nom'];
            $prenom = $visiteur['prenom'];
            $roleId = $visiteur['id_role'];
            
            // On enregistre les infos dans $_SESSION pour "garder" la connexion
            Utilitaires::connecter($id, $nom, $prenom);
            
            // On enregistre aussi le rôle (Comptable ou Visiteur) pour gérer les droits d'accès
            Utilitaires::setRole($roleId);
            
            // Redirection vers la page d'accueil (index.php?uc=accueil)
            header('Location: index.php');
        }
        break;

    /**
     * Action par défaut
     */
    default:
        include PATH_VIEWS . 'v_connexion.php';
        break;
}
