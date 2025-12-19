<div class="container">
    <div class="structure-hero pt-lg-5 pt-4">
        <h1 class="titre text-center">Mes Rapports de Visite</h1>
    </div>

    <div class="row align-items-center justify-content-center">
        <div class="col-12 m-0 mt-4">

            <?php if (empty($result)): ?>
                <!-- Message si aucun résultat trouvé -->
                <div class="alert alert-info mt-3 text-center">
                    Vous n'avez aucun rapport validé ou consulté pour le moment.
                </div>
            <?php else: ?>
                <!-- Table des résultats -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Num</th>
                                <th>Praticien</th>
                                <th>Motif</th>
                                <th>Date</th>
                                <th>Médicaments</th>
                                <th>État</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $rapport): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rapport['RAP_NUM']); ?></td>
                                    <td><?php echo htmlspecialchars('(' . $rapport['PRA_NUM'] . ') ' . $rapport['PRA_NOM'] . ' ' . $rapport['PRA_PRENOM']); ?>
                                    </td>
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
                                            $meds[] = $rapport['CODE1'] . ' - ' . $rapport['MED1'];
                                        if (!empty($rapport['MED2']))
                                            $meds[] = $rapport['CODE2'] . ' - ' . $rapport['MED2'];
                                        echo htmlspecialchars(implode(', ', $meds));
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Affichage de l'état (Badge)
                                        $etatLib = isset($rapport['ETAT_LIBELLE']) ? $rapport['ETAT_LIBELLE'] : 'Clos';
                                        $badgeClass = ($rapport['ET_CODE'] == 2) ? 'bg-success' : 'bg-primary';
                                        echo '<span class="badge ' . $badgeClass . '">' . htmlspecialchars($etatLib) . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <a href="index.php?uc=rapportvisite&action=afficherrapport&rapports=<?php echo $rapport['RAP_NUM']; ?>&matricule=<?php echo $rapport['COL_MATRICULE']; ?>"
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