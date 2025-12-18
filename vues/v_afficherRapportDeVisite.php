<section class="bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card shadow border-0 rounded-3">
                    <div class="card-header text-white p-4 d-flex justify-content-between align-items-center"
                        style="background-color: #123ea6;">
                        <div>
                            <h2 class="h4 mb-0 text-white">Rapport de Visite
                                N°<?php echo htmlspecialchars($carac[3]); ?></h2>
                            <small class="opacity-75">Saisi le <?php echo htmlspecialchars($carac[7]); ?></small>
                        </div>
                        <span class="badge bg-light text-dark fs-6"
                            style="color: #123ea6 !important;"><?php echo htmlspecialchars($carac[18]); ?></span>
                    </div>

                    <div class="card-body p-4">
                        <!-- Infos Collaborateur & Visite -->
                        <div class="row mb-4">
                            <div class="col-md-6 border-end">
                                <h5 class="mb-3" style="color: #123ea6;">Collaborateur</h5>
                                <p class="mb-1"><strong>Nom :</strong>
                                    <?php echo htmlspecialchars($carac[1] . ' ' . $carac[2]); ?></p>
                                <p class="mb-0 text-muted small"><strong>Matricule :</strong>
                                    <?php echo htmlspecialchars($carac[0]); ?></p>
                            </div>
                            <div class="col-md-6 ps-md-4 mt-3 mt-md-0">
                                <h5 class="mb-3" style="color: #123ea6;">Visite</h5>
                                <p class="mb-1"><strong>Date de visite :</strong>
                                    <?php echo htmlspecialchars($carac[4]); ?></p>
                                <p class="mb-0"><strong>Motif :</strong> <span
                                        class="badge bg-info text-dark"><?php echo htmlspecialchars($carac[5]); ?></span>
                                </p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Infos Praticien -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3" style="color: #123ea6;">Praticien</h5>
                                <div class="bg-light p-3 rounded">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">
                                                <?php echo htmlspecialchars($carac[9] . ' ' . $carac[10]); ?>
                                            </h6>
                                            <small class="text-muted">Numéro :
                                                <?php echo htmlspecialchars($carac[8]); ?></small>
                                        </div>
                                        <a href="index.php?uc=praticien&action=afficherprat&praticien=<?php echo $carac[8]; ?>"
                                            class="btn btn-sm btn-outline-primary">Voir détails</a>
                                    </div>
                                    <?php if (!empty($carac[12])): ?>
                                        <div class="mt-2 pt-2 border-top">
                                            <p class="mb-0 small text-muted"><strong>Remplaçant :</strong>
                                                <?php echo htmlspecialchars($carac[12] . ' ' . $carac[13]); ?>
                                                (<?php echo htmlspecialchars($carac[11]); ?>)</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Bilan -->
                        <div class="mb-4">
                            <h5 class="mb-3" style="color: #123ea6;">Bilan</h5>
                            <div class="p-3 bg-light rounded border-start border-4"
                                style="border-color: #123ea6 !important;">
                                <p class="mb-0 fst-italic">"<?php echo nl2br(htmlspecialchars_decode($carac[6])); ?>"
                                </p>
                            </div>
                        </div>

                        <!-- Médicaments Présentés -->
                        <?php if (!empty($carac[14]) || !empty($carac[16])): ?>
                            <div class="mb-4">
                                <h5 class="mb-3" style="color: #123ea6;">Médicaments Présentés</h5>
                                <div class="list-group">
                                    <?php if (!empty($carac[14])): ?>
                                        <a href="index.php?uc=medicaments&action=affichermedoc&medicament=<?php echo $carac[14]; ?>"
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <span><?php echo htmlspecialchars($carac[15]); ?></span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($carac[16])): ?>
                                        <a href="index.php?uc=medicaments&action=affichermedoc&medicament=<?php echo $carac[16]; ?>"
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <span><?php echo htmlspecialchars($carac[17]); ?></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Échantillons Offerts -->
                        <?php if (!empty($echantillons)): ?>
                            <div class="mb-4">
                                <h5 class="mb-3" style="color: #123ea6;">Échantillons Offerts</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Médicament (Code)</th>
                                                <th class="text-center" style="width: 100px;">Quantité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($echantillons as $echantillon): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($echantillon['MED_DEPOTLEGAL']); ?></td>
                                                    <td class="text-center fw-bold" style="color: #123ea6;">
                                                        <?php echo htmlspecialchars($echantillon['QTE']); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="card-footer bg-light p-4 d-flex justify-content-between">
                        <a href="index.php?uc=rapportvisite&action=voirrapport" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Retour
                        </a>

                        <?php if ($carac[19] == 0): // État Nouveau ?>
                            <a href="index.php?uc=rapportvisite&action=editerrapport&rapports=<?php echo $carac[3]; ?>"
                                class="btn btn-warning text-white">
                                <i class="bi bi-pencil-square me-2"></i>Modifier
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>