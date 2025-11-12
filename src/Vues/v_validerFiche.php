<?php ?>
<!-- Utilise container-fluid et utilitaires Bootstrap pour supprimer tout padding horizontal -->
<div class="container-fluid px-0">

    <!-- sélection visiteur / mois (collée à gauche) -->
    <div class="row mb-4 gx-0">
        <div class="col-12">
            <div class="card shadow-sm border-0 ms-0 col-md-4">
                <div class="card-body py-3 px-3">
                    <form method="post" action="index.php?uc=validerFiche&action=chargerFiche" class="row gx-2 gy-2 align-items-end">
                        <div class="col-auto">
                            <label for="visiteur" class="form-label small mb-1">Choisir le visiteur</label>
                            <select class="form-select form-select-sm" id="visiteur" name="visiteur" required>
                                <?php if (isset($tousVisiteurs)) {
                                    foreach ($tousVisiteurs as $v) { ?>
                                        <option value="<?php echo htmlspecialchars($v['id']); ?>" <?php if (isset($idVisiteur) && $idVisiteur === $v['id']) { echo 'selected'; } ?>>
                                            <?php echo htmlspecialchars($v['nom'] . ' ' . $v['prenom']); ?>
                                        </option>
                                <?php }
                                } ?>
                            </select>
                        </div>

                        <div class="col-auto">
                            <label for="mois" class="form-label small mb-1">Mois</label>
                            <select class="form-select form-select-sm" id="mois" name="mois">
                                <?php if (isset($lesMois)) {
                                    foreach ($lesMois as $m) { ?>
                                        <option value="<?php echo htmlspecialchars($m['mois']); ?>" <?php if (isset($mois) && $mois === $m['mois']) { echo 'selected'; } ?>>
                                            <?php echo htmlspecialchars($m['numMois'] . '-' . $m['numAnnee']); ?>
                                        </option>
                                <?php }
                                } ?>
                            </select>
                        </div>

                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm" type="submit">Charger</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Titre collé tout à gauche -->
    <div class="row mb-3 gx-0">
        <div class="col-12">
            <h2 class="mb-4 text-warning ms-0 ps-0">Valider la fiche de frais</h2>
        </div>
    </div>

    <!-- Contenu principal : tout à gauche (colonne gauche contenant forfait + hors-forfait) -->
    <div class="row gx-0">
        <!-- Colonne gauche : ps-0 pour supprimer padding gauche -->
        <div class="col-12 col-lg-4 ps-0">
            <!-- Forfait card (collée à gauche) -->
            <div class="card shadow-sm mb-4 ms-0">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0">Éléments forfaitisés</h6>
                </div>
                <div class="card-body">
                    <?php if (isset($lesFraisForfait)) { ?>
                        <form method="post" action="index.php?uc=validerFiche&action=valider" role="form">
                            <input type="hidden" name="visiteur" value="<?php echo htmlspecialchars($idVisiteur); ?>">
                            <input type="hidden" name="mois" value="<?php echo htmlspecialchars($mois); ?>">

                            <?php foreach ($lesFraisForfait as $unFrais) { ?>
                                <div class="mb-3">
                                    <label class="form-label small text-secondary"><?php echo htmlspecialchars($unFrais['libelle']); ?></label>
                                    <input type="text" class="form-control form-control-sm" name="lesFrais[<?php echo htmlspecialchars($unFrais['idfrais']); ?>]" value="<?php echo htmlspecialchars($unFrais['quantite']); ?>">
                                </div>
                            <?php } ?>

                            <div class="mb-3">
                                <label class="form-label small text-secondary">Total estimé</label>
                                <input type="text" class="form-control form-control-sm" value="<?php echo isset($montantCalcule) ? htmlspecialchars(number_format($montantCalcule, 2, ',', ' ')) . ' €' : ''; ?>" readonly>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-sm" type="submit">Valider</button>
                                <button class="btn btn-danger btn-sm" type="reset">Réinitialiser</button>
                            </div>
                        </form>
                    <?php } else { ?>
                        <p class="text-muted small mb-0">Aucun élément forfaitisé à afficher.</p>
                    <?php } ?>
                </div>
            </div>

            <!-- Hors-forfait card placé directement sous le forfait, collée à gauche -->
            <div class="card shadow-sm ms-0 border-warning">
            <div class="card-header p-0">
                <div class="bg-warning text-white py-2 px-3">
                    <h6 class="mb-0">Descriptif des éléments hors forfait</h6>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 align-middle border-warning">
                        <thead class="bg-warning text-white">
                            <tr>
                                <th style="width:25%;">Date</th>
                                <th style="width:45%;">Libellé</th>
                                <th style="width:15%;">Montant</th>
                                <th style="width:15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($lesFraisHorsForfait) && count($lesFraisHorsForfait) > 0) {
                                foreach ($lesFraisHorsForfait as $hf) { ?>
                                    <tr>
                                        <td class="align-middle">
                                            <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($hf['date']); ?>" readonly>
                                        </td>
                                        <td class="align-middle">
                                            <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($hf['libelle']); ?>" readonly>
                                        </td>
                                        <td class="align-middle">
                                            <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars(number_format((float)$hf['montant'], 2, '.', '')); ?>" readonly>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex flex-column gap-2">
                                                <a class="btn btn-success btn-sm" href="index.php?uc=validerFiche&action=corrigerHF&idFrais=<?php echo htmlspecialchars($hf['id']); ?>&visiteur=<?php echo htmlspecialchars($idVisiteur); ?>&mois=<?php echo htmlspecialchars($mois); ?>">Corriger</a>
                                                <a class="btn btn-danger btn-sm" href="index.php?uc=validerFiche&action=refuserHF&idFrais=<?php echo htmlspecialchars($hf['id']); ?>&visiteur=<?php echo htmlspecialchars($idVisiteur); ?>&mois=<?php echo htmlspecialchars($mois); ?>">Refuser</a>
                                            </div>
                                        </td>
                                    </tr>
                            <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">Aucun frais hors forfait pour ce visiteur / mois.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div> <!-- card-body -->
        </div> <!-- card hors-forfait -->
             <!-- Bas : justificatifs et boutons (boutons en dessous, espacés) -->
            <div class="p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label class="form-label small mb-0">Nombre de justificatifs :</label>
                    </div>
                    <div class="col-auto" style="min-width:90px;">
                        <input type="number" class="form-control form-control-sm" value="<?php echo isset($nbJustificatifs) ? htmlspecialchars($nbJustificatifs) : '0'; ?>">
                    </div>
                </div>

                <!-- boutons sur une nouvelle ligne, alignés à gauche et espacés -->
                <div class="row mt-3">
                    <div class="col">
                        <div class="d-flex gap-3">
                            <button class="btn btn-success btn-sm" type="submit">Valider</button>
                            <button class="btn btn-secondary btn-sm" type="reset">Réinitialiser</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Colonne droite vide (pas d'espace à gauche) -->
        <div class="col-12 col-lg-8"></div>
    </div> <!-- row -->

</div> <!-- container-fluid -->