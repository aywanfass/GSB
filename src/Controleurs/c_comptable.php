<?php

use Outils\Utilitaires;

// Interdire l'accès aux non-comptables
Utilitaires::exigerRole('COMPT');

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'tableauBord';

switch ($action) {
    case 'tableauBord':
        // Ici, chargez vos données (ex: fiches à valider)
        include PATH_VIEWS . 'v_comptable_dashboard.php';
        break;

    // Exemples d'autres actions:
    // case 'validerFiche': ...
    // case 'rembourserFiche': ...

    default:
        include PATH_VIEWS . 'v_comptable_dashboard.php';
        break;
}