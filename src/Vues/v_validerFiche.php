<?php ?>
<!-- Utilise container-fluid et utilitaires Bootstrap pour supprimer tout padding horizontal -->
<div class="container-fluid px-0">

    <!-- sélection visiteur / mois -->
    <div class="row mb-4">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <form id="chargerFicheForm" method="post" action="index.php?uc=validerFiche&action=chargerFiche" class="row gx-3 gy-2 align-items-end">
                        <div class="col-12 col-sm-6">
                            <label for="visiteur" class="form-label small mb-1 fw-bold text-secondary">Choisir le visiteur</label>
                            <select class="form-select form-select-sm border-warning" id="visiteur" name="visiteur" required>
                                <option value="">Choisir un visiteur</option>
                                <?php
                                if (isset($tousVisiteurs) && is_array($tousVisiteurs)) {
                                    foreach ($tousVisiteurs as $v) {
                                        // Respect des noms de clés attendus : 'id', 'nom', 'prenom'
                                        $optId = htmlspecialchars($v['id']);
                                        $optLabel = htmlspecialchars($v['nom'] . ' ' . $v['prenom']);
                                        $selected = (isset($idVisiteur) && ((string) $idVisiteur === (string) $v['id'])) ? 'selected' : '';
                                        echo '<option value="' . $optId . '" ' . $selected . '>' . $optLabel . '</option>';
                                    }
                                } else {
                                    ?>
                                    <option value="" selected>Aucun visiteur</option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label for="mois" class="form-label small mb-1 fw-bold text-secondary">Mois</label>
                            <select class="form-select form-select-sm border-warning" id="mois" name="mois" aria-label="Choisir le mois">
                                <?php
                                if (isset($lesMois) && is_array($lesMois)) {
                                    foreach ($lesMois as $m) {
                                        // Chaque élément $m doit contenir : 'mois' (yyyymm), 'numMois', 'numAnnee'
                                        $optVal = htmlspecialchars($m['mois']);
                                        $optLabel = htmlspecialchars($m['numMois'] . '-' . $m['numAnnee']);
                                        $selected = (isset($mois) && ((string) $mois === (string) $m['mois'])) ? 'selected' : '';
                                        echo '<option value="' . $optVal . '" ' . $selected . '>' . $optLabel . '</option>';
                                    }
                                } else {
                                    ?>
                                    <option value="" selected>Aucun mois</option>
                                <?php } ?>
                            </select>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Titre -->
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="mb-4 text-warning border-bottom pb-2">Valider la fiche de frais</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-4 mb-4">
            <div class="card shadow-sm border-warning h-100">
                <div class="card-header bg-warning text-white py-2">
                    <h6 class="mb-0">Éléments forfaitisés</h6>
                </div>
                <div class="card-body">
                    <?php if (isset($lesFraisForfait)) { ?>
                        <form method="post" action="index.php?uc=validerFiche&action=valider" role="form">
                            <input type="hidden" name="visiteur" value="<?php echo htmlspecialchars($idVisiteur); ?>">
                            <input type="hidden" name="mois" value="<?php echo htmlspecialchars($mois); ?>">

                            <?php foreach ($lesFraisForfait as $unFrais) { ?>
                                <div class="mb-3">
                                    <label class="form-label small text-secondary fw-bold"><?php echo htmlspecialchars($unFrais['libelle']); ?></label>
                                    <input type="text" class="form-control form-control-sm border-warning" name="lesFrais[<?php echo htmlspecialchars($unFrais['idfrais']); ?>]" value="<?php echo htmlspecialchars($unFrais['quantite']); ?>">
                                </div>
                            <?php } ?>

                            <div class="mb-3">
                                <label class="form-label small text-secondary fw-bold">Total estimé</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control border-warning bg-light" value="<?php echo isset($montantCalcule) ? htmlspecialchars(number_format($montantCalcule, 2, ',', ' ')) : ''; ?>" readonly>
                                    <span class="input-group-text bg-warning border-warning text-white">€</span>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-sm w-100" type="submit">Valider</button>
                                <button class="btn btn-outline-danger btn-sm w-100" type="reset">Réinitialiser</button>
                            </div>
                        </form>
                    <?php } else { ?>
                        <p class="text-muted small mb-0 text-center py-4">Aucun élément forfaitisé à afficher.</p>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Hors-forfait card -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card shadow-sm border-warning h-100">
                <div class="card-header bg-warning text-white py-2 px-3">
                    <h6 class="mb-0">Descriptif des éléments hors forfait</h6>
                </div>
                <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0 align-middle border-warning">
                    <thead class="bg-warning text-white">
                        <tr>
                            <th>Date</th>
                            <th>Libellé</th>
                            <th>Montant</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($lesFraisHorsForfait) && count($lesFraisHorsForfait) > 0) {
                            // On parcourt chaque frais hors forfait
                            foreach ($lesFraisHorsForfait as $hf) {
                                $libelle = $hf['libelle'];
                                // Règle BTS : On détecte si le frais est refusé (commence par "REFUSE ")
                                $estRefuse = (strpos($libelle, 'REFUSE ') === 0);
                                
                                // Si refusé, on applique les classes CSS d'opacité et de texte barré
                                $classeLigne = '';
                                if ($estRefuse) {
                                    $classeLigne = 'opacity-50 text-decoration-line-through';
                                }
                                ?>
                                <tr class="<?php echo $classeLigne; ?>">
                                    <td class="align-middle">
                                        <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($hf['date']); ?>" readonly>
                                    </td>
                                    <td class="align-middle">
                                        <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($libelle); ?>" readonly>
                                    </td>
                                    <td class="align-middle">
                                        <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars(number_format((float) $hf['montant'], 2, '.', '')); ?>" readonly>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex gap-2">
                                            <?php if (!$estRefuse) { ?>
                                                <a class="btn btn-success btn-sm text-decoration-none" href="index.php?uc=validerFiche&action=corrigerHF&idFrais=<?php echo htmlspecialchars($hf['id']); ?>&visiteur=<?php echo htmlspecialchars($idVisiteur); ?>&mois=<?php echo htmlspecialchars($mois); ?>">Corriger</a>
                                                <a class="btn btn-danger btn-sm text-decoration-none" href="index.php?uc=validerFiche&action=refuserHF&idFrais=<?php echo htmlspecialchars($hf['id']); ?>&visiteur=<?php echo htmlspecialchars($idVisiteur); ?>&mois=<?php echo htmlspecialchars($mois); ?>">Refuser</a>
                                            <?php } else { ?>
                                                <span class="badge bg-secondary">Refusé</span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">Aucun frais hors forfait pour ce visiteur / mois.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
                </div> <!-- card-body -->
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-sm border-warning">
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="form-label small mb-0 fw-bold">Nombre de justificatifs :</label>
                        </div>
                        <div class="col-auto" style="min-width:120px;">
                            <input type="number" class="form-control form-control-sm border-warning" value="<?php echo isset($nbJustificatifs) ? htmlspecialchars($nbJustificatifs) : '0'; ?>">
                        </div>
                        <div class="col-auto ms-auto">
                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-sm px-4" type="submit">Valider la fiche</button>
                                <button class="btn btn-outline-secondary btn-sm px-4" type="reset">Réinitialiser</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div> <!-- container-fluid -->

<script src="js/menu_deroulant.js"></script>