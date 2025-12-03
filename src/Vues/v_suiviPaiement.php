<?php ?>
<div>
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="text-warning">Suivi des paiements</h2>
        </div>
    </div>

    <!-- Filtre + Statistiques -->
    <div class="row mb-3">
        <div class="">
            <div class="card   mb-3 col-lg-3">
                <div class="card-body">
                    <form id="chargerFicheForm" method="get" action="index.php" role="form" class="row gx-2 gy-2 align-items-end">
                        <input type="hidden" name="uc" value="suiviPaiement">
                        <input type="hidden" name="action" value="lister">
                        <div class="col-12 col-sm-8">
                            <label for="visiteur" class="form-label small">Visiteur</label>
                            <select class="form-select form-select-sm" id="visiteur" name="visiteur">
                                <option value="">Tous</option>
                                <?php if (isset($tousVisiteurs)) { foreach ($tousVisiteurs as $v) { ?>
                                    <option value="<?php echo htmlspecialchars($v['id']); ?>" <?php if (isset($filtreVisiteur) && $filtreVisiteur === $v['id']) { echo 'selected'; } ?>>
                                        <?php echo htmlspecialchars($v['nom'] . ' ' . $v['prenom']); ?>
                                    </option>
                                <?php }} ?>
                            </select>
                        </div>
                        <div class=" col-sm-4 text-sm-end">
                            <button class="btn btn-primary btn-sm mt-2 mt-sm-0" type="submit">Filtrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card ">
                <div class="card-body">
                    <h4 class="mb-3 small">Statistiques (<?php echo htmlspecialchars($annee ?? date('Y')); ?>)</h4>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="bg-warning text-white small">
                                <tr>
                                    <th>Mois</th>
                                    <th>Total remboursé (€)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($statsRemboursements)) { foreach ($statsRemboursements as $s) { 
                                    $m = $s['mois']; $mm = substr($m, 4, 2); $yyyy = substr($m, 0, 4);
                                ?>
                                    <tr>
                                        <td class="small"><?php echo htmlspecialchars($mm . '-' . $yyyy); ?></td>
                                        <td class="small"><?php echo htmlspecialchars(number_format((float)$s['total'], 2, ',', ' ')); ?></td>
                                    </tr>
                                <?php }} ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <!-- Fiches validées -->
    <div class="row">
        <div class="col-12">
            <div class="  col-md-8">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 align-middle border-warning">
                            <caption class="caption-top text-warning fw-bold fs-4 text-start mb-3">Fiches validées</caption>
                            <thead class="bg-warning text-white">
                                <tr>
                                    <th>Visiteur</th>
                                    <th>Mois</th>
                                    <th>Montant validé (€)</th>
                                    <th>Justifs</th>
                                    <th>Dernière modif</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($fichesValidees)) { foreach ($fichesValidees as $f) { 
                                    $m = $f['mois']; $mm = substr($m, 4, 2); $yyyy = substr($m, 0, 4);
                                ?>
                                    <tr>
                                        <td class="small"><?php echo htmlspecialchars($f['nom'] . ' ' . $f['prenom']); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($mm . '-' . $yyyy); ?></td>
                                        <td class="small"><?php echo htmlspecialchars(number_format((float)$f['montantvalide'], 2, ',', ' ')); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($f['nbjustificatifs']); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($f['datemodif']); ?></td>
                                        <td>
                                            <form method="post" action="index.php?uc=suiviPaiement&action=payer" class="row gx-2 gy-2 align-items-center" role="form">
                                                <input type="hidden" name="idVisiteur" value="<?php echo htmlspecialchars($f['idvisiteur']); ?>">
                                                <input type="hidden" name="mois" value="<?php echo htmlspecialchars($f['mois']); ?>">
                                                <div class="col-auto">
                                                    <label class="form-label small mb-1">Date</label>
                                                    <input type="date" name="datePaiement" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-auto">
                                                    <label class="form-label small mb-1">Réf.</label>
                                                    <input type="text" name="refPaiement" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-auto">
                                                    <button type="submit" class="btn btn-success btn-sm mt-2 mt-sm-0">Payer</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                <?php }} ?>
                            </tbody>
                        </table>
                    </div>
                </div> <!-- card-body -->
            </div> <!-- card -->
        </div>
    </div>
</div>

<script src="/js/Menu_deroulant.js"></script>