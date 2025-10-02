<?php
/**
 * Vue État de Frais (Bootstrap 5.3)
 */
?>
<hr>
<div class="card border-primary mb-4">
    <div class="card-header bg-primary text-white">
        Fiche de frais du mois <?php echo htmlspecialchars($numMois . '-' . $numAnnee); ?>
    </div>
    <div class="card-body">
        <p class="mb-1">
            <strong>État :</strong> <?php echo htmlspecialchars($libEtat); ?>
            depuis le <?php echo htmlspecialchars($dateModif); ?>
        </p>
        <p class="mb-0">
            <strong>Montant validé :</strong> <?php echo htmlspecialchars($montantValide); ?>
        </p>
    </div>
</div>

<div class="card border-info mb-4">
    <div class="card-header bg-info text-white">Éléments forfaitisés</div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <?php foreach ($lesFraisForfait as $unFraisForfait) { ?>
                        <th><?php echo htmlspecialchars($unFraisForfait['libelle']); ?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php foreach ($lesFraisForfait as $unFraisForfait) { ?>
                        <td class="qteForfait"><?php echo htmlspecialchars($unFraisForfait['quantite']); ?></td>
                    <?php } ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-info mb-4">
    <div class="card-header bg-info text-white">
        Descriptif des éléments hors forfait - <?php echo htmlspecialchars($nbJustificatifs); ?> justificatifs reçus
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Libellé</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lesFraisHorsForfait as $unFraisHorsForfait) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($unFraisHorsForfait['date']); ?></td>
                    <td><?php echo htmlspecialchars($unFraisHorsForfait['libelle']); ?></td>
                    <td><?php echo htmlspecialchars($unFraisHorsForfait['montant']); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>