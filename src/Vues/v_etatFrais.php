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
                <?php
                // Étape 1 : Affichage de l'état actuel de la fiche (Saisie en cours, Validée, etc.)
                ?>
                <p class="mb-2"><strong>État :</strong> <span class="badge bg-info text-dark"><?php echo $libEtat ?></span></p>
                
                <?php
                // Étape 2 : Date de la dernière mise à jour par le comptable ou le système
                ?>
                <p class="mb-2 text-muted x-small italic">Dernière modification le <?php echo $dateModif ?></p>
                
                <?php
                // Étape 3 : Montant validé (affiché seulement après validation par le comptable)
                ?>
                <p class="mb-3"><strong>Montant validé :</strong> <span class="text-primary fw-bold"><?php echo $montantValide ?> €</span></p>
                
                <?php 
                // Étape 4 : Gestion du bouton PDF (Règle Green-IT)
                // On récupère le code de l'état (VA = Validé, RB = Remboursé)
                $idEtat = $lesInfosFicheFrais['idEtat'];
                $peutTelecharger = false;
                if ($idEtat == 'VA' || $idEtat == 'RB') {
                    $peutTelecharger = true;
                }
                
                // On définit les classes CSS du bouton (on ajoute 'disabled' si non téléchargeable)
                $classeBouton = "btn btn-outline-primary btn-sm";
                if (!$peutTelecharger) {
                    $classeBouton = $classeBouton . " disabled";
                }
                ?>
                
                <a class="<?php echo $classeBouton; ?>" 
                   href="telecharger_pdf.php?mois=<?php echo htmlspecialchars($leMois); ?>"
                   title="<?php echo !$peutTelecharger ? 'Disponible uniquement après validation' : 'Télécharger le PDF'; ?>">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Télécharger PDF
                </a>

                <?php 
                // Petit message d'explication si le bouton est grisé
                if (!$peutTelecharger) {
                ?>
                    <p class="text-muted x-small mt-2 italic">
                        <i class="bi bi-info-circle me-1"></i> Le PDF sera disponible une fois la fiche mise en paiement.
                    </p>
                <?php } ?>
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
                    $libelle = $unFraisHorsForfait['libelle'];
                    $estRefuse = (strpos($libelle, 'REFUSE ') === 0);
                    $classRefuse = $estRefuse ? 'opacity-50 text-decoration-line-through' : '';
                    $montant = $unFraisHorsForfait['montant']; ?>
                    <tr class="<?php echo $classRefuse; ?>">
                        <td class="small"><?php echo htmlspecialchars($date) ?></td>
                        <td class="small"><?php echo htmlspecialchars($libelle) ?></td>
                        <td class="small text-end fw-bold"><?php echo htmlspecialchars(number_format((float)$montant, 2, ',', ' ')) ?> €</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>