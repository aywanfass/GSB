<?php
/**
 * Accueil (intégré dans le layout sidebar)
 */
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';
$role = $_SESSION['role'] ?? null;
?>
<?php if ($role === 'VIS') : ?>
    <div class="alert alert-info border-info shadow-sm d-flex align-items-center" role="alert">
        <i class="bi bi-info-circle-fill me-3 fs-4"></i>
        <div>
            <strong>Rappel :</strong> Vos frais doivent être saisis avant la fin du mois. Factures reçues après le 10 → mois suivant.
        </div>
    </div>
    <h2 class="h4 mb-4 border-bottom pb-2">
        Tableau de bord
        <small class="text-muted d-block d-sm-inline fs-6 fw-normal ms-sm-2">
            Visiteur médical : <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
        </small>
    </h2>
    <div class="row g-3">
        <div class="col-md-6 col-lg-5">
            <a href="index.php?uc=gererFrais&action=saisirFrais" class="text-decoration-none">
                <div class="card card-visitor h-100 border-primary shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-primary text-white rounded p-3 me-3">
                            <i class="bi bi-pencil-square fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title text-primary mb-1">Renseigner fiche de frais</h5>
                            <p class="card-text small text-muted mb-0">Saisir vos frais forfaitisés et hors forfait du mois en cours.</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-5">
            <a href="index.php?uc=etatFrais&action=selectionnerMois" class="text-decoration-none">
                <div class="card card-visitor h-100 border-primary shadow-sm">
                    <div class="card-body d-flex align-items-end">
                        <div class="bg-primary text-white rounded p-3 me-3">
                            <i class="bi bi-card-list fs-3"></i>
                        </div>
                        <div>
                            <h5 class="card-title text-primary mb-1">Consulter mes fiches</h5>
                            <p class="card-text small text-muted mb-0">Visualiser l'historique et le statut de vos remboursements.</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
 <?php else : ?>
    <h2 class="h4 mb-4">
        Gestion des frais
        <small class="text-muted d-block d-sm-inline">
            Comptable : <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
        </small>
    </h2>
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <a href="index.php?uc=validerFiche&action=afficher" class="text-decoration-none">
                <div class="card h-100 border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <i class="bi bi-pencil-square me-1"></i> Valider les fiches de frais
                        </h5>
                        <p class="card-text small mb-0">Valider les frais.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="index.php?uc=suiviPaiement&action=afficher" class="text-decoration-none">
                <div class="card h-100 border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <i class="bi bi-card-list me-1"></i> Suivre le paiement des fiches de frais
                        </h5>
                        <p class="card-text small mb-0">Payer les fiches de frais.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

 <?php endif; ?>
