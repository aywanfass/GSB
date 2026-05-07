<?php

/**
 * Vue Liste des frais hors forfait
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
<hr>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-primary mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 fs-6">Descriptif des éléments hors forfait</h5>
            </div>
            <?php
            // Règle de gestion 2025 : Rappel sur la TVA et les factures acquittées
            ?>
            <div class="alert alert-info border-0 rounded-0 mb-0 small py-2 px-3">
                <i class="bi bi-info-circle me-1"></i>
                <strong>Notice 2025 :</strong> Tout frais « hors forfait » doit être dûment justifié par l’envoi d’une facture acquitée faisant apparaître le montant de TVA. Les originaux doivent être conservés 3 ans.
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Date</th>
                            <th class="small">Libellé</th>  
                            <th class="small text-end">Montant</th>  
                            <th class="small text-center">Action</th> 
                        </tr>
                    </thead>  
                    <tbody>
                    <?php
                    foreach ($lesFraisHorsForfait as $unFraisHorsForfait) {
                        $libelle = htmlspecialchars($unFraisHorsForfait['libelle']);
                        $date = $unFraisHorsForfait['date'];
                        $montant = $unFraisHorsForfait['montant'];
                        $id = $unFraisHorsForfait['id']; ?>           
                        <tr>
                            <td class="small"> <?php echo $date ?></td>
                            <td class="small"> <?php echo $libelle ?></td>
                            <td class="small text-end fw-bold"> <?php echo htmlspecialchars(number_format((float)$montant, 2, ',', ' ')) ?> €</td>
                            <td class="text-center">
                                <a href="index.php?uc=gererFrais&action=supprimerFrais&idFrais=<?php echo $id ?>" 
                                   class="btn btn-outline-danger btn-sm"
                                   onclick="return confirm('Voulez-vous vraiment supprimer ce frais?');">
                                    <i class="bi bi-trash me-1"></i> Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>  
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row mb-5">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 fs-6">
                    <i class="bi bi-plus-circle me-2"></i>
                    Nouvel élément hors forfait
                </h5>
            </div>
            <div class="card-body">
                <form action="index.php?uc=gererFrais&action=validerCreationFrais" method="post" role="form">
                    <div class="mb-3">
                        <label for="txtDateHF" class="form-label small fw-bold text-secondary">Date (jj/mm/aaaa) :</label>
                        <input type="date" id="txtDateHF" name="dateFrais" class="form-control form-control-sm border-primary">
                    </div>
                    <div class="mb-3">
                        <label for="txtLibelleHF" class="form-label small fw-bold text-secondary">Libellé :</label>             
                        <input type="text" id="txtLibelleHF" name="libelle" class="form-control form-control-sm border-primary" placeholder="Ex: Restaurant, Taxi...">
                    </div> 
                    <div class="mb-4">
                        <label for="txtMontantHF" class="form-label small fw-bold text-secondary">Montant :</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="txtMontantHF" name="montant" class="form-control border-primary" value="">
                            <span class="input-group-text bg-primary border-primary text-white">€</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm px-4" type="submit">Ajouter</button>
                        <button class="btn btn-outline-danger btn-sm px-4" type="reset">Effacer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>