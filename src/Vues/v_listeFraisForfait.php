<?php

/**
 * Vue Liste des frais au forfait
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
<h2 class="h4 mb-4 border-bottom pb-2">Renseigner ma fiche de frais du mois <?php echo $numMois . '-' . $numAnnee ?></h2>

<div class="row">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 fs-6">
                    <i class="bi bi-box me-2"></i>
                    Éléments forfaitisés
                </h5>
            </div>
            <div class="card-body">
                <form method="post" action="index.php?uc=gererFrais&action=validerMajFraisForfait" role="form">
                    <?php
                    foreach ($lesFraisForfait as $unFrais) {
                        $idFrais = $unFrais['idfrais'];
                        $libelle = htmlspecialchars($unFrais['libelle']);
                        $quantite = $unFrais['quantite']; ?>
                        <div class="mb-3">
                            <label for="idFrais_<?php echo $idFrais ?>" class="form-label small fw-bold text-secondary"><?php echo $libelle ?></label>
                            <input type="text" id="idFrais_<?php echo $idFrais ?>" 
                                   name="lesFrais[<?php echo $idFrais ?>]"
                                   size="10" maxlength="5" 
                                   value="<?php echo $quantite ?>" 
                                   class="form-control form-control-sm border-primary">
                        </div>
                        <?php
                    }
                    ?>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm px-4" type="submit">Valider</button>
                        <button class="btn btn-outline-danger btn-sm px-4" type="reset">Effacer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>