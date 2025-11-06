<?php ?>
<div class="row">
    <h2>Suivi des paiements</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <form method="get" action="index.php" role="form">
            <input type="hidden" name="uc" value="suiviPaiement">
            <input type="hidden" name="action" value="lister">
            <div class="form-group">
                <label for="visiteur">Visiteur</label>
                <select class="form-control" id="visiteur" name="visiteur">
                    <option value="">Tous</option>
                    <?php if (isset($tousVisiteurs)) { foreach ($tousVisiteurs as $v) { ?>
                        <option value="<?php echo htmlspecialchars($v['id']); ?>" <?php if (isset($filtreVisiteur) && $filtreVisiteur === $v['id']) { echo 'selected'; } ?>>
                            <?php echo htmlspecialchars($v['nom'] . ' ' . $v['prenom']); ?>
                        </option>
                    <?php }} ?>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Filtrer</button>
        </form>
    </div>
    <div class="col-md-6">
        <h4>Statistiques (<?php echo htmlspecialchars($annee ?? date('Y')); ?>)</h4>
        <table class="table table-bordered table-responsive">
            <thead>
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
                        <td><?php echo htmlspecialchars($mm . '-' . $yyyy); ?></td>
                        <td><?php echo htmlspecialchars(number_format((float)$s['total'], 2, ',', ' ')); ?></td>
                    </tr>
                <?php }} ?>
            </tbody>
        </table>
    </div>
    </div>

<hr>
<div class="row">
    <div class="col-md-12">
        <h3>Fiches validées</h3>
        <table class="table table-bordered table-responsive">
            <thead>
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
                        <td><?php echo htmlspecialchars($f['nom'] . ' ' . $f['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($mm . '-' . $yyyy); ?></td>
                        <td><?php echo htmlspecialchars(number_format((float)$f['montantvalide'], 2, ',', ' ')); ?></td>
                        <td><?php echo htmlspecialchars($f['nbjustificatifs']); ?></td>
                        <td><?php echo htmlspecialchars($f['datemodif']); ?></td>
                        <td>
                            <form method="post" action="index.php?uc=suiviPaiement&action=payer" class="form-inline" role="form">
                                <input type="hidden" name="idVisiteur" value="<?php echo htmlspecialchars($f['idvisiteur']); ?>">
                                <input type="hidden" name="mois" value="<?php echo htmlspecialchars($f['mois']); ?>">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" name="datePaiement" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Réf.</label>
                                    <input type="text" name="refPaiement" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-success">Payer</button>
                            </form>
                        </td>
                    </tr>
                <?php }} ?>
            </tbody>
        </table>
    </div>
</div>