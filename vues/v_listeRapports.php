<div class="container">
    <div class="structure-hero pt-lg-5 pt-4">
        <h1 class="titre text-center">Historique des rapports de visite</h1>
    </div>

    <div class="row align-items-center justify-content-center">
        <!-- 
            TABLEAU D'AFFICHAGE DES RAPPORTS
            N'apparaît que si l'utilisateur est habilité.
        -->
        <div class="col-12 m-0 mt-4">

            <?php if (empty($result)): ?>
                <!-- Message si aucun résultat trouvé -->
                <div class="alert alert-warning mt-3 text-center">
                    Aucun rapport trouvé avec ces critères.
                    <br>
                    <a href="index.php?uc=rapportvisite&action=voirrapport" class="btn btn-secondary mt-2">Retour à la
                        sélection</a>
                </div>
            <?php else: ?>
                <!-- Table des résultats -->
                <div class="d-flex justify-content-end mb-3">
                    <a href="index.php?uc=rapportvisite&action=voirrapport" class="btn btn-secondary">Retour à la
                        sélection</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Num</th>
                                <th>Visiteur</th>
                                <th>Praticien</th>
                                <th>Motif</th>
                                <th>Date</th>
                                <th>Médicaments</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $rapport): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rapport['RAP_NUM']); ?></td>
                                    <td><?php echo htmlspecialchars($rapport['COL_NOM'] . ' ' . $rapport['COL_PRENOM']); ?></td>
                                    <td><?php echo htmlspecialchars($rapport['PRA_NOM'] . ' ' . $rapport['PRA_PRENOM']); ?></td>
                                    <td>
                                        <?php
                                        echo htmlspecialchars($rapport['MO_LIBELLE']);
                                        if (!empty($rapport['RAP_MOTIF_AUTRE'])) {
                                            echo ' (' . htmlspecialchars($rapport['RAP_MOTIF_AUTRE']) . ')';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($rapport['RAP_DATEVISITE'])); ?></td>
                                    <td>
                                        <?php
                                        $meds = [];
                                        if (!empty($rapport['MED1']))
                                            $meds[] = $rapport['MED1'];
                                        if (!empty($rapport['MED2']))
                                            $meds[] = $rapport['MED2'];
                                        echo htmlspecialchars(implode(', ', $meds));
                                        ?>
                                    </td>
                                    <td>
                                        <a href="index.php?uc=rapportvisite&action=afficherrapport&rapports=<?php echo $rapport['RAP_NUM']; ?>"
                                            class="btn btn-sm btn-info text-white">Voir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted"><?php echo count($result); ?> rapport(s) trouvé(s)</small>
            <?php endif; ?>
        </div>
    </div>
</div>