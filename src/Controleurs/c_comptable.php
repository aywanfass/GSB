<?php

use Outils\Utilitaires;

// Interdire l'accès aux non-comptables
Utilitaires::exigerRole('COMPT');

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

switch ($action) {
    case 'validerFiches':
        include PATH_VIEWS . 'v_comptable_validation.php';
        break;

    case 'suivrePaiement':
        include PATH_VIEWS . 'v_comptable_suivre_paiement.php';
        break;

    default:
        include PATH_VIEWS . 'v_acceuil.php';
        break;
}