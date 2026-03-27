<?php
/**
 * Vue Liste des frais au forfait (Bootstrap 5.3)
 */
?>
<div class="row mb-3">
    <div class="col-12">
        <h2>Renseigner ma fiche de frais du mois <?php echo htmlspecialchars($numMois . '-' . $numAnnee); ?></h2>
        <h3>Éléments forfaitisés</h3>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <form method="post"
              action="index.php?uc=gererFrais&action=validerMajFraisForfait">
            <fieldset>
                <?php foreach ($lesFraisForfait as $unFrais) {
                    $idFrais = $unFrais['idfrais'];
                    $libelle = htmlspecialchars($unFrais['libelle']);
                    $quantite = htmlspecialchars($unFrais['quantite']); ?>
                    <div class="mb-3">
                        <label for="frais_<?php echo $idFrais; ?>" class="form-label">
                            <?php echo $libelle; ?>
                        </label>
                        <input type="text"
                               id="frais_<?php echo $idFrais; ?>"
                               name="lesFrais[<?php echo $idFrais; ?>]"
                               maxlength="5"
                               value="<?php echo $quantite; ?>"
                               class="form-control">
                    </div>
                <?php } ?>
                <button class="btn btn-success me-2" type="submit">Valider</button>
                <button class="btn btn-danger" type="reset">Effacer</button>
            </fieldset>
        </form>
    </div>
</div>