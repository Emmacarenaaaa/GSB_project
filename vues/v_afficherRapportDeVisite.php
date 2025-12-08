<section class="bg-light">
    <div class="container">
        <div class="structure-hero pt-lg-5 pt-4">
            <h1 class="titre text-center">Détails du rapport de visite</h1>
        </div>
        <div class="row align-items-center justify-content-center">
            <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5">
                <img class="img-fluid" src="assets/img/medoc.jpeg">
            </div>
            <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5 py-3">
                <div class="formulaire">
                    <p><span class="carac">Numéro du collaborateur</span> : <?php echo htmlspecialchars($carac[0]); ?>
                    </p>
                    <p><span class="carac">Nom</span> : <?php echo htmlspecialchars($carac[1]); ?></p>
                    <p><span class="carac">Prénom</span> : <?php echo htmlspecialchars($carac[2]); ?></p>

                    <p><span class="carac">Numéro du rapport de visite</span> :
                        <?php echo htmlspecialchars($carac[3]); ?>
                    </p>
                    <p><span class="carac">Date de visite</span> : <?php echo htmlspecialchars($carac[4]); ?></p>
                    <p><span class="carac">Motif</span> : <?php echo htmlspecialchars($carac[5]); ?></p>

                    <p><span class="carac">Bilan</span> : <?php echo htmlspecialchars($carac[6]); ?></p>
                    <p><span class="carac">Date de saisie</span> : <?php echo htmlspecialchars($carac[7]); ?></p>


                    <p><span class="carac">Numéro du praticien</span> : <?php echo htmlspecialchars($carac[8]); ?></p>
                    <p><span class="carac">Nom du praticien</span> : <a
                            href="index.php?uc=praticien&action=afficherprat&praticien=<?php echo $carac[8]; ?>"><?php echo htmlspecialchars($carac[9]); ?></a>
                    </p>
                    <p><span class="carac">Prénom du praticien</span> : <?php echo htmlspecialchars($carac[10]); ?></p>

                    <?php if (!empty($carac[12])): ?>
                        <p><span class="carac">Numéro du remplaçant</span> : <?php echo htmlspecialchars($carac[11]); ?></p>
                        <p><span class="carac">Nom du remplaçant</span> : <?php echo htmlspecialchars($carac[12]); ?></p>
                        <p><span class="carac">Prénom du remplaçant</span> : <?php echo htmlspecialchars($carac[13]); ?></p>
                    <?php else: ?>
                        <p><span class="carac">Remplaçant</span> : Non défini(e)</p>
                    <?php endif; ?>

                    <?php if (!empty($carac[14])): ?>
                        <p><span class="carac">Médicament présenté 1</span> : <a
                                href="index.php?uc=medicaments&action=affichermedoc&medicament=<?php echo $carac[14]; ?>"><?php echo htmlspecialchars($carac[14] . ' - ' . $carac[15]); ?></a>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($carac[16])): ?>
                        <p><span class="carac">Médicament présenté 2</span> : <a
                                href="index.php?uc=medicaments&action=affichermedoc&medicament=<?php echo $carac[16]; ?>"><?php echo htmlspecialchars($carac[16] . ' - ' . $carac[17]); ?></a>
                        </p>
                    <?php endif; ?>

                    <p><span class="carac">État du rapport</span> : <?php echo htmlspecialchars($carac[18]); ?></p>

                    <?php if ($carac[19] == 1): // 19 est l'index de ET_CODE (ajouté à la fin) ?>
                        <div class="mt-4 text-center">
                            <a href="index.php?uc=rapportvisite&action=editerrapport&rapports=<?php echo $carac[3]; ?>"
                                class="btn btn-warning text-white">Modifier le rapport</a>
                            <a href="index.php?uc=rapportvisite&action=voirrapport"
                                class="btn btn-secondary ms-2">Retour</a>
                        </div>
                    <?php else: ?>
                        <div class="mt-4 text-center">
                            <a href="index.php?uc=rapportvisite&action=voirrapport" class="btn btn-secondary">Retour</a>
                        </div>
                    <?php endif; ?>


                </div>
            </div>
        </div>
    </div>
</section>