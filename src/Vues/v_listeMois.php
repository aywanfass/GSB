<?php
/**
 * Vue Liste des mois (Bootstrap 5.3)
 */
?>
<h2>Mes fiches de frais</h2>
<div class="row">
    <div class="col-md-4">
        <h3>Sélectionner un mois :</h3>
    </div>
    <div class="col-md-4">
        <form action="index.php?uc=etatFrais&action=voirEtatFrais" method="post">
            <div class="mb-3">
                <label for="lstMois" class="form-label" accesskey="n">Mois :</label>
                <select id="lstMois" name="lstMois" class="form-select">
                    <?php foreach ($lesMois as $unMois) {
                        $mois = $unMois['mois'];
                        $numAnnee = $unMois['numAnnee'];
                        $numMois = $unMois['numMois'];
                        $selected = ($mois == $moisASelectionner) ? 'selected' : '';
                        ?>
                        <option value="<?php echo htmlspecialchars($mois); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($numMois . '/' . $numAnnee); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button id="ok" type="submit" class="btn btn-success me-2">Valider</button>
            <button id="annuler" type="reset" class="btn btn-danger">Effacer</button>
        </form>
    </div>
</div>