<?php ?>
<div class="container-fluid px-0">

    <!-- sélection visiteur -->
    <div class="ms-0 col-md-3 row mb-4 gx-0 card-body py-3 px-3">
        <form method="get" id="chargerFicheForm" action="index.php" role="form" class="row gx-2 gy-2 align-items-end">
            <input type="hidden" name="uc" value="suiviPaiement">
            <input type="hidden" name="action" value="lister">
            <div class="col-12 col-sm-8">
                <label for="visiteur" class="form-label small mb-1 fw-bold">Choisir le visiteur :</label>
                <select class="form-select form-select-sm" id="visiteur" name="visiteur">
                    <option value="">Tous</option>
                    <?php
                    if (isset($tousVisiteurs)) {
                        foreach ($tousVisiteurs as $v) {
                            ?>
                            <option value="<?php echo htmlspecialchars($v['id']); ?>" <?php
                            if (isset($filtreVisiteur) && $filtreVisiteur === $v['id']) {
                                echo 'selected';
                            }
                            ?>>
                                        <?php echo htmlspecialchars($v['nom'] . ' ' . $v['prenom']); ?>
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </div>
        </form> 
    </div>

    <!-- Titre -->
    <div class="row mb-3 gx-0">
        <div class="col-12">
            <h2 class="mb-4 text-warning ms-0 ps-0">Suivi des paiements</h2>
        </div>
    </div>

    <!-- Fiches validées -->
    <div class="card shadow-sm ms-0 border-warning col-12 col-lg-7">
        <div class="card-header p-0">
            <div class="bg-warning text-white py-2 px-3">
                <h6 class="mb-0">Fiches validées</h6>
            </div>
        </div>
        
        <div class="table-responsive card-body p-0 overflow-hidden">
            <table class="table table-bordered table-sm mb-0 align-middle border-warning">
                <thead class="bg-warning text-white">
                    <tr>
                        <th>Visiteur</th>
                        <th>Mois</th>
                        <th>Montant validé (€)</th>
                        <th>Justificatifs</th>
                        <th>Dernière modification</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($fichesValidees)) {
                        foreach ($fichesValidees as $f) {
                            $m = $f['mois'];
                            $mm = substr($m, 4, 2);
                            $yyyy = substr($m, 0, 4);
                            ?>
                            <tr>
                                <td class="small"><?php echo htmlspecialchars($f['nom'] . ' ' . $f['prenom']); ?></td>
                                <td class="small"><?php echo htmlspecialchars($mm . '-' . $yyyy); ?></td>
                                <td class="small"><?php echo htmlspecialchars(number_format((float) $f['montantvalide'], 2, ',', ' ')); ?></td>
                                <td class="small"><?php echo htmlspecialchars($f['nbjustificatifs']); ?></td>
                                <td class="small"><?php echo htmlspecialchars($f['datemodif']); ?></td>
                                <td>
                                    <form method="post" action="index.php?uc=suiviPaiement&action=payer" class="row gx-2 gy-2 align-items-center" role="form">
                                        <input type="hidden" name="idVisiteur" value="<?php echo htmlspecialchars($f['idvisiteur']); ?>">
                                        <input type="hidden" name="mois" value="<?php echo htmlspecialchars($f['mois']); ?>">
                                        <div class="ms-3">
                                            <button type="submit" class="btn btn-success btn-sm">Payer</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>    
</div>

<script src="/js/Menu_deroulant.js"></script>