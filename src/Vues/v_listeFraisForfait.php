<?php
/**
 * Vue Liste des frais au forfait (Bootstrap 5.3)
 */
?>
<?php if ($role === 'VIS') : ?>
    <div class="row">
        <h2>Renseigner ma fiche de frais du mois <?php echo htmlspecialchars($numMois . '-' . $numAnnee); ?></h2>
        <h3>Éléments forfaitisés</h3>
        <div class="col-md-4">
            <form method="post"
                  action="index.php?uc=gererFrais&action=validerMajFraisForfait">
                <fieldset>
                    <?php
                    foreach ($lesFraisForfait as $unFrais) {
                        $idFrais = $unFrais['idfrais'];
                        $libelle = htmlspecialchars($unFrais['libelle']);
                        $quantite = htmlspecialchars($unFrais['quantite']);
                        ?>
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
                    <button class="btn btn-success me-2" type="submit">Ajouter</button>
                    <button class="btn btn-danger" type="reset">Effacer</button>
                </fieldset>
            </form>
        </div>
    </div>
    /**
    */
<?php else : ?>
    <div class="row">
        <div class="col-md-8">
            <form class="row g-3 align-items-center mb-4" method="get" action="index.php">
                <input type="hidden" name="uc" value="gererFrais">
                <input type="hidden" name="action" value="validerFiches">
                <div class="col-auto d-flex align-items-center">
                    <label for="visiteur" class="me-2 mb-0">Choisir le visiteur :</label>
                    <select id="visiteur" name="visiteur" class="form-select form-select-sm w-auto">
                        <?php if (!empty($lesVisiteurs)) : ?>
                            <?php foreach ($lesVisiteurs as $v) { ?>
                                <?php
                                $id = $v['idVisiteurs'];
                                $nomVisiteurs = htmlspecialchars($v['nom']);
                                ?>
                                <option value="<?= $id ?>"><?= $nomVisiteurs ?></option>
                            <?php }; ?>
                        <?php else: ?>
                            <option value="<?= htmlspecialchars($id) ?>" selected>Visiteur</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-auto d-flex align-items-center">
                    <label for="mois" class="me-2 mb-0">Mois :</label>
                    <select id="mois" name="mois" class="form-select form-select-sm w-auto">
                        <?php
                        // Construit 12 derniers mois (yyyymm => mm/yyyy)
                        $dt = new DateTime('first day of this month');
                        for ($i = 0; $i < 12; $i++) {
                            $key = $dt->format('Ym');
                            $label = $dt->format('m/Y');
                            $selected = ($key === $mois) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($key) . '" ' . $selected . '>' . htmlspecialchars($label) . '</option>';
                            $dt->modify('-1 month');
                        }
                        ?>
                    </select>
                </div>
            </form>
        </div>
        <h2 class=" mb-3 text-warning">Valider la fiche de frais</h2>
        <div class="col-md-4 mb-3">
            <h3>Éléments forfaitisés</h3>
            <form method="post"
                  action="index.php?uc=gererFrais&action=validerMajFraisForfait">
                <fieldset>
                    <?php
                    foreach ($lesFraisForfait as $unFrais) {
                        $idFrais = $unFrais['idfrais'];
                        $libelle = htmlspecialchars($unFrais['libelle']);
                        $quantite = htmlspecialchars($unFrais['quantite']);
                        ?>
                        <div class="mb-3 col-lg-4">
                            <label for="frais_<?php echo $idFrais; ?>" class="form-label">
                                <h6><?php echo $libelle; ?></h6>
                            </label>
                            <input type="text"
                                   id="frais_<?php echo $idFrais; ?>"
                                   name="lesFrais[<?php echo $idFrais; ?>]"
                                   maxlength="5"
                                   value="<?php echo $quantite; ?>"
                                   class="form-control">
                        </div>
                    <?php } ?>
                    <button class="btn btn-success me-2" type="submit">Corriger</button>
                    <button class="btn btn-danger" type="reset">Réinitialiser</button>
                </fieldset>
            </form>
        </div>
    </div>
<?php endif; ?>