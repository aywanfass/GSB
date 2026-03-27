<?php

/**
 * Vue État de Frais
 *
 * PHP Version 8
 *
 * @category  PPE
 * @package   GSB
 * @author    Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.reseaucerta.org Contexte « Laboratoire GSB »
 * @link      https://getbootstrap.com/docs/3.3/ Documentation Bootstrap v3
 */

?>
<div class="row mb-4">
    <div class="col-12 col-md-6">
        <div class="card shadow-sm border-primary h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 fs-6">
                    <i class="bi bi-info-circle me-2"></i>
                    Fiche de frais du mois <?php echo $numMois . '-' . $numAnnee ?>
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>État :</strong> <span class="badge bg-info text-dark"><?php echo $libEtat ?></span></p>
                <p class="mb-2 text-muted x-small italic">Dernière modification le <?php echo $dateModif ?></p>
                <p class="mb-3"><strong>Montant validé :</strong> <span class="text-primary fw-bold"><?php echo $montantValide ?> €</span></p>
                
                <a class="btn btn-outline-primary btn-sm" href="telecharger_pdf.php?mois=<?php echo htmlspecialchars($leMois); ?>">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Télécharger PDF
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-primary mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0 fs-6">Éléments forfaitisés</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <?php
                    foreach ($lesFraisForfait as $unFraisForfait) {
                        $libelle = $unFraisForfait['libelle']; ?>
                        <th class="text-center small"> <?php echo htmlspecialchars($libelle) ?></th>
                        <?php
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    foreach ($lesFraisForfait as $unFraisForfait) {
                        $quantite = $unFraisForfait['quantite']; ?>
                        <td class="text-center"><?php echo $quantite ?> </td>
                        <?php
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm border-primary mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fs-6">Descriptif des éléments hors forfait</h5>
        <span class="badge bg-light text-primary"><?php echo $nbJustificatifs ?> justificatif(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="small">Date</th>
                    <th class="small">Libellé</th>
                    <th class="small text-end">Montant</th>                
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($lesFraisHorsForfait as $unFraisHorsForfait) {
                    $date = $unFraisHorsForfait['date'];
                    $libelle = htmlspecialchars($unFraisHorsForfait['libelle']);
                    $montant = $unFraisHorsForfait['montant']; ?>
                    <tr>
                        <td class="small"><?php echo $date ?></td>
                        <td class="small"><?php echo $libelle ?></td>
                        <td class="small text-end fw-bold"><?php echo htmlspecialchars(number_format((float)$montant, 2, ',', ' ')) ?> €</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>