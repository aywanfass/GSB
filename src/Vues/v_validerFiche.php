<?php ?>
<div class="row">
    <h2>Validation des fiches</h2>
</div>
<div class="row">
    <div class="col-md-6">
        <form method="post" action="index.php?uc=validerFiche&action=chargerFiche" role="form">
            <div class="form-group">
                <label for="visiteur">Visiteur</label>
                <select class="form-control" id="visiteur" name="visiteur" required>
                    <?php if (isset($tousVisiteurs)) { foreach ($tousVisiteurs as $v) { ?>
                        <option value="<?php echo htmlspecialchars($v['id']); ?>" <?php if (isset($idVisiteur) && $idVisiteur === $v['id']) { echo 'selected'; } ?>>
                            <?php echo htmlspecialchars($v['nom'] . ' ' . $v['prenom']); ?>
                        </option>
                    <?php }} ?>
                </select>
            </div>
            <div class="form-group">
                <label for="mois">Mois</label>
                <select class="form-control" id="mois" name="mois">
                    <?php if (isset($lesMois)) { foreach ($lesMois as $m) { ?>
                        <option value="<?php echo htmlspecialchars($m['mois']); ?>" <?php if (isset($mois) && $mois === $m['mois']) { echo 'selected'; } ?>>
                            <?php echo htmlspecialchars($m['numMois'] . '-' . $m['numAnnee']); ?>
                        </option>
                    <?php }} ?>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Charger</button>
        </form>
    </div>
</div>

<?php if (isset($lesFraisForfait) && isset($lesFraisHorsForfait)) { ?>
<hr>
<div class="row">
    <div class="col-md-6">
        <h3>Forfait</h3>
        <form method="post" action="index.php?uc=validerFiche&action=valider" role="form">
            <input type="hidden" name="visiteur" value="<?php echo htmlspecialchars($idVisiteur); ?>">
            <input type="hidden" name="mois" value="<?php echo htmlspecialchars($mois); ?>">
            <?php foreach ($lesFraisForfait as $unFrais) { ?>
                <div class="form-group">
                    <label><?php echo htmlspecialchars($unFrais['libelle']); ?></label>
                    <input type="text" class="form-control" name="lesFrais[<?php echo htmlspecialchars($unFrais['idfrais']); ?>]" value="<?php echo htmlspecialchars($unFrais['quantite']); ?>">
                </div>
            <?php } ?>
            <div class="form-group">
                <label>Total estimé</label>
                <input type="text" class="form-control" value="<?php echo isset($montantCalcule) ? htmlspecialchars(number_format($montantCalcule, 2, ',', ' ')) : ''; ?> €" readonly>
            </div>
            <button class="btn btn-success" type="submit">Valider</button>
        </form>
    </div>
    <div class="col-md-6">
        <h3>Hors forfait</h3>
        <table class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Libellé</th>
                    <th>Montant</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lesFraisHorsForfait as $hf) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($hf['date']); ?></td>
                    <td><?php echo htmlspecialchars($hf['libelle']); ?></td>
                    <td><?php echo htmlspecialchars($hf['montant']); ?></td>
                    <td>
                        <a class="btn btn-xs btn-warning" href="index.php?uc=validerFiche&action=refuserHF&idFrais=<?php echo htmlspecialchars($hf['id']); ?>&visiteur=<?php echo htmlspecialchars($idVisiteur); ?>&mois=<?php echo htmlspecialchars($mois); ?>">Refuser</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>