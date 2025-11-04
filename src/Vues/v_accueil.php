<?php
/**
 * Accueil (intégré dans le layout sidebar)
 */
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';
$role = $_SESSION['role'] ?? null;
?>
<?php if ($role === 'VIS') : ?>
    <div class="alert alert-warning" role="alert">
        <strong>Rappel :</strong> Vos frais doivent être saisis avant la fin du mois. Factures reçues après le 10 → mois suivant.
    </div>
    <h2 class="h4 mb-4">
        Gestion des frais
        <small class="text-muted d-block d-sm-inline">
            Visiteur : <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?>
        </small>
    </h2>
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <a href="index.php?uc=gererFrais&action=saisirFrais" class="text-decoration-none">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <h5 class="card-title text-success">
                            <i class="bi bi-pencil-square me-1"></i> Fiche de frais
                        </h5>
                        <p class="card-text small mb-0">Renseigner les frais du mois en cours.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="index.php?uc=etatFrais&action=selectionnerMois" class="text-decoration-none">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="bi bi-card-list me-1"></i> Mes fiches
                        </h5>
                        <p class="card-text small mb-0">Consulter l’historique et le statut.</p>
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
            <a href="index.php?uc=gererFrais&action=saisirFrais" class="text-decoration-none">
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
            <a href="index.php?uc=etatFrais&action=selectionnerMois" class="text-decoration-none">
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
