<?php
/**
 * Sidebar de navigation (Bootstrap 5.3)
 * Utilise Bootstrap Icons.
 * Variables disponibles : $uc (use-case courant)
 */
?>
<nav id="sidebar" class="app-sidebar p-3">
    <h5 class="d-flex align-items-center mb-3">
        <i class="bi bi-diagram-3-fill me-2 text-primary"></i>
        <span>GSB Intranet</span>
    </h5>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
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
    </ul>
    <hr>
    <?php if (isset($_SESSION['prenom'], $_SESSION['nom'])) { ?>
        <div class="small text-muted">
            <i class="bi bi-person-badge me-1"></i>
            <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?>
        </div>
    <?php } ?>
</nav>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>