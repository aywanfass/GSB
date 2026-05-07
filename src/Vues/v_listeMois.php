<?php
/**
 * Vue Liste des mois (Bootstrap 5.3)
 */
?>
<h2 class="h4 mb-4 border-bottom pb-2">Mes fiches de frais</h2>

<div class="row mb-4">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 fs-6">
                    <i class="bi bi-calendar-check me-2"></i>
                    Sélectionner un mois
                </h5>
            </div>
            <div class="card-body">
                <form action="index.php?uc=etatFrais&action=voirEtatFrais" method="post" role="form">
                    <div class="mb-3">
                        <label for="lstMois" class="form-label small fw-bold text-secondary" accesskey="n">Mois :</label>
                        <select id="lstMois" name="lstMois" class="form-select form-select-sm border-primary">
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
                    <div class="d-flex gap-2">
                        <button id="ok" type="submit" class="btn btn-success btn-sm px-4">Valider</button>
                        <button id="annuler" type="reset" class="btn btn-outline-danger btn-sm px-4">Effacer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>