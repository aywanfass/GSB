<?php
/**
 * Vue Liste des frais hors forfait (Bootstrap 5.3)
 */
?>
<hr>
<div class="row">
    <div class="col-12">
        <div class="card border-info mb-4">
            <div class="card-header bg-info text-white">
                Descriptif des éléments hors forfait
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Libellé</th>
                            <th>Montant</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lesFraisHorsForfait as $unFraisHorsForfait) {
                        $libelle = htmlspecialchars($unFraisHorsForfait['libelle']);
                        $date = htmlspecialchars($unFraisHorsForfait['date']);
                        $montant = htmlspecialchars($unFraisHorsForfait['montant']);
                        $id = $unFraisHorsForfait['id']; ?>
                        <tr>
                            <td><?php echo $date; ?></td>
                            <td><?php echo $libelle; ?></td>
                            <td><?php echo $montant; ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-danger"
                                   href="index.php?uc=gererFrais&action=supprimerFrais&idFrais=<?php echo urlencode($id); ?>"
                                   onclick="return confirm('Voulez-vous vraiment supprimer ce frais ?');">
                                    Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="col-12">
        <h3>Nouvel élément hors forfait</h3>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <form action="index.php?uc=gererFrais&action=validerCreationFrais" method="post">
            <div class="mb-3">
                <label for="txtDateHF" class="form-label">Date :</label>
                <input type="date" id="txtDateHF" name="dateFrais" class="form-control">
            </div>
            <div class="mb-3">
                <label for="txtLibelleHF" class="form-label">Libellé</label>
                <input type="text" id="txtLibelleHF" name="libelle" class="form-control">
            </div>
            <div class="mb-3">
                <label for="txtMontantHF" class="form-label">Montant :</label>
                <div class="input-group">
                    <input type="text" id="txtMontantHF" name="montant" class="form-control" value="">
                    <span class="input-group-text">€</span>
                </div>
            </div>
            <button class="btn btn-success me-2" type="submit">Ajouter</button>
            <button class="btn btn-secondary" type="reset">Réinitialiser</button>
        </form>
    </div>
</div>