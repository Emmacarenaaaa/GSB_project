<section class="bg-light">
    <div class="container">
        <div class="structure-hero pt-lg-5 pt-4">
            <h1 class="titre text-center">Gestion des praticiens</h1>
            <p class="text text-center">
                Interface de gestion permettant au délégué de créer, modifier
                et consulter les praticiens de son secteur.
            </p>
        </div>

        <div class="row align-items-center justify-content-center mt-5">
            <!-- Single success message shown after Valider -->
            <?php if ($mode === 'succes' && !empty($message)): ?>
                <div class="col-12">
                    <div class="alert alert-success text-center" role="alert">
                        <?= htmlspecialchars($message) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Bouton de retour vers le formulaire principal des praticiens -->
            <div class="col-12 text-center mb-3">
                <form method="get" action="index.php" style="display:inline-block;">
                    <input type="hidden" name="uc" value="praticien">
                    <input type="hidden" name="action" value="formulaireprat">
                    <button type="submit" class="btn btn-outline-primary">Retour sur le formulaire de
                        praticiens</button>
                </form>
            </div>

            <!-- Affichage des erreurs -->
            <?php if (!empty($errors)): ?>
                <div class="col-12">
                    <div class="alert alert-danger" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ===== MODE LISTE ===== -->
            <?php if ($mode == 'liste'): ?>
                <div class="col-12 col-lg-8">
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <h3 class="card-title mb-4">Gestion des praticiens</h3>

                            <form method="post" action="index.php?uc=praticien&action=modif">
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="praticien_select">Sélectionner un praticien
                                        :</label>
                                    <select name="praticien_select" id="praticien_select" class="form-select mt-2" required>
                                        <option value="">-- Choisissez un praticien --</option>
                                        <?php foreach ($praticiens as $prat): ?>
                                            <option value="<?= htmlspecialchars($prat['PRA_NUM']) ?>">
                                                <?= htmlspecialchars($prat['PRA_NUM'] . ' - ' . $prat['PRA_NOM'] . ' ' . $prat['PRA_PRENOM']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning text-light">Modifier</button>
                                </div>
                            </form>

                            <form method="post" class="mt-3">
                                <input type="hidden" name="form_action" value="creer">
                                <button type="submit" class="btn btn-success">Créer un nouveau praticien</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ===== MODE EDITER ===== -->
            <?php if ($mode == 'editer' && $infosPrat !== null): ?>
                <div class="col-12 col-lg-8">
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <h3 class="card-title mb-4">
                                <?= empty($infosPrat['matriculepraticien']) ? 'Créer un nouveau praticien' : 'Modifier le praticien' ?>
                            </h3>

                            <form id="formPraticien" method="post" action="index.php?uc=praticien&action=modif">
                                <input type="hidden" name="form_action" value="valider">
                                <input type="hidden" name="pranum"
                                    value="<?= htmlspecialchars($infosPrat['matriculepraticien'] ?? '') ?>">

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="pranom">Nom * :</label>
                                    <input type="text" name="pranom" id="pranom" class="form-control"
                                        value="<?= htmlspecialchars($infosPrat['nom'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="praprenom">Prénom * :</label>
                                    <input type="text" name="praprenom" id="praprenom" class="form-control"
                                        value="<?= htmlspecialchars($infosPrat['prenom'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="praadresse">Adresse :</label>
                                    <input type="text" name="praadresse" id="praadresse" class="form-control"
                                        value="<?= htmlspecialchars($infosPrat['adresse'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="pracp">Code postal :</label>
                                    <input type="text" name="pracp" id="pracp" class="form-control"
                                        value="<?= htmlspecialchars($infosPrat['codepostal'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="praville">Ville :</label>
                                    <input type="text" name="praville" id="praville" class="form-control"
                                        value="<?= htmlspecialchars($infosPrat['ville'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="pracoefnotoriete">Coefficient notoriété :</label>
                                    <input type="number" name="pracoefnotoriete" id="pracoefnotoriete" class="form-control"
                                        step="0.01"
                                        value="<?= htmlspecialchars($infosPrat['coefficientdenotoriete'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="pracoefconfiance">Coefficient confiance :</label>
                                    <input type="number" name="pracoefconfiance" id="pracoefconfiance" class="form-control"
                                        step="0.01"
                                        value="<?= htmlspecialchars($infosPrat['coefficientdeconfiance'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="typcode">Type de praticien * :</label>
                                    <select name="typcode" id="typcode" class="form-select" required>
                                        <option value="">-- Sélectionnez un type --</option>
                                        <?php foreach ($types as $type): ?>
                                            <?php $selected = (!empty($infosPrat['typedepraticien']) && $infosPrat['typedepraticien'] == $type['TYP_LIBELLE']) ? 'selected' : ''; ?>
                                            <option value="<?= htmlspecialchars($type['TYP_CODE']) ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($type['TYP_LIBELLE']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Spécialités :</label>
                                    <div class="border p-3 rounded">
                                        <?php foreach ($specialites as $spe): ?>
                                            <?php $checked = in_array($spe['SPE_CODE'], $specialites_prat) ? 'checked' : ''; ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="specialites[]"
                                                    id="spe_<?= htmlspecialchars($spe['SPE_CODE']) ?>"
                                                    value="<?= htmlspecialchars($spe['SPE_CODE']) ?>" <?= $checked ?>>
                                                <label class="form-check-label"
                                                    for="spe_<?= htmlspecialchars($spe['SPE_CODE']) ?>">
                                                    <?= htmlspecialchars($spe['SPE_LIBELLE']) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Valider</button>
                                </div>
                            </form>

                            <form method="post" class="mt-3">
                                <input type="hidden" name="form_action" value="liste">
                                <button type="submit" class="btn btn-secondary">Annuler</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- MODE 'succes' UI removed; single alert above handles success display -->
        </div>
    </div>
</section>
<script src="assets/js/modifPraticien.js"></script>