<?php

/**
 * Contrôleur "Accueil"
 * 
 * Ce fichier gère l'affichage de la page d'accueil après la connexion.
 * Il sert simplement d'aiguillage vers la vue de bienvenue.
 */

// Si l'utilisateur est connecté, on affiche la vue d'accueil
if ($estConnecte) {
    include PATH_VIEWS . 'v_accueil.php';
} else {
    // Sinon, on le redirige vers la connexion (Sécurité)
    include PATH_VIEWS . 'v_connexion.php';
}
