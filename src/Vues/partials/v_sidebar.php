<?php
/**
 * Sidebar de navigation (Bootstrap 5.3)
 * Utilise Bootstrap Icons.
 * Variables disponibles : $uc (use-case courant)
 *
 * Affiche des onglets différents si l'utilisateur a le rôle 'COMPT'
 * (stocké dans $_SESSION['role']).
 */
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';
$role = $_SESSION['role'] ?? null;
?>
<nav id="sidebar" class="app-sidebar p-3">
    <h5 class="d-flex align-items-center mb-3">
        <img src="./images/logo.jpg" class="img-fluid d-block mx-auto" alt="Laboratoire Galaxy-Swiss Bourdin" style="max-height:75px;">
    </h5>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <?php if ($role === 'COMPT') : ?>
           <li class="nav-item ">
                    <a href="index.php"
                       class="nav-link <?php echo (!$uc || $uc === 'accueil') ? 'active bg-warning ' : 'text-warning'; ?>">
                        <i class="bi bi-house"></i>
                        Accueil
                    </a>
            </li>
            <li class="nav-item">
                <a href="index.php?uc=comptable&action=validerFiches"
                   class="nav-link <?php echo ($uc === 'comptable' && $action === 'validerFiches') ? 'active bg-warning ' : 'text-warning'; ?>">
                    <i class="bi bi-check2-square"></i>
                    Valider les fiches de frais
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?uc=comptable&action=suivrePaiement"
                   class="nav-link <?php echo ($uc === 'comptable' && $action === 'suivrePaiement') ? 'active bg-warning ' : 'text-warning'; ?>">
                    <i class="bi bi-cash-stack"></i>
                    Suivre le paiement des fiches de frais
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?uc=deconnexion&action=demandeDeconnexion"
                   class="nav-link <?php echo ($uc === 'deconnexion') ? 'active bg-warning ' : 'text-warning'; ?>">
                    <i class="bi bi-box-arrow-right"></i>
                    Déconnexion
                </a>
            </li>
        <?php else : ?>
            <li class="nav-item">
                <a href="index.php"
                   class="nav-link <?php echo (!$uc || $uc === 'accueil') ? 'active' : ''; ?>">
                    <i class="bi bi-house"></i>
                    Accueil
                </a>
            </li>
            <li>
                <a href="index.php?uc=gererFrais&action=saisirFrais"
                   class="nav-link <?php echo ($uc === 'gererFrais') ? 'active' : ''; ?>">
                    <i class="bi bi-pencil-square"></i>
                    Fiche de frais
                </a>
            </li>
            <li>
                <a href="index.php?uc=etatFrais&action=selectionnerMois"
                   class="nav-link <?php echo ($uc === 'etatFrais') ? 'active' : ''; ?>">
                    <i class="bi bi-card-list"></i>
                    Mes fiches
                </a>
            </li>
            <li>
                <a href="index.php?uc=deconnexion&action=demandeDeconnexion"
                   class="nav-link <?php echo ($uc === 'deconnexion') ? 'active' : ''; ?>">
                    <i class="bi bi-box-arrow-right"></i>
                    Déconnexion
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <hr>
    <?php if (isset($_SESSION['prenom'], $_SESSION['nom'])) { ?>
        <div class="small text-muted">
            <i class="bi bi-person-badge me-1"></i>
            <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?>
            <?php if ($role) { ?>&nbsp;–&nbsp;<strong><?php echo htmlspecialchars($role); ?></strong><?php } ?>
        </div>
    <?php } ?>
</nav>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>