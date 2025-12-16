<div class="row align-items-center justify-content-center">
    <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5 py-3">
        <form id="formRapport" action="index.php?uc=rapportvisite&action=sauvegarderModification" method="post"
            class="formulaire-recherche col-12 m-0" onsubmit="return validerFormulaire();">
            <h2 class="text-center mb-4">Modifier le rapport n°<?php echo $rapNum; ?></h2>

            <input type="hidden" name="rapNum" value="<?php echo $rapNum; ?>">

            <label for="praticien">Praticien <span style="color:red;">*</span> :</label>
            <select name="praticien" id="praticien" class="form-select" required>
                <?php foreach ($praticiens as $praticien) {
                    $selected = ($praticien['PRA_NUM'] == $carac[8]) ? 'selected' : '';
                    echo '<option value="' . $praticien['PRA_NUM'] . '" ' . $selected . '>' . htmlspecialchars($praticien['PRA_NOM'] . ' ' . $praticien['PRA_PRENOM']) . '</option>';
                } ?>
            </select>
            <br>

            <label for="dateVisite">Date de visite <span style="color:red;">*</span> :</label>
            <input type="date" name="dateVisite" id="dateVisite" class="form-control"
                value="<?php echo htmlspecialchars($carac[4]); ?>" required>
            <br>

            <label for="motif">Motif <span style="color:red;">*</span> :</label>
            <select name="motif" id="motif" required class="form-select" onchange="toggleMotifAutre()">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($motifs as $motif) {
                    $selected = '';
                    // Si le libellé correspond
                    if ($motif['MO_LIBELLE'] == $carac[5]) {
                        $selected = 'selected';
                    }

                    echo '<option value="' . $motif['MO_CODE'] . '" ' . $selected . '>' . htmlspecialchars($motif['MO_LIBELLE']) . '</option>';
                } ?>
            </select> <br><br>

            <div id="motifAutreContainer" style="display:none;">
                <label for="motif_autre">Précisez le motif <span style="color:red;">*</span> :</label>
                <input type="text" name="motif_autre" id="motif_autre" maxlength="50" class="form-control"
                    value="<?php echo ($carac[5] != 'Autre' && !in_array($carac[5], array_column($motifs, 'MO_LIBELLE'))) ? htmlspecialchars($carac[5]) : ''; ?>">
                <small class="text-muted">Maximum 50 caractères</small>
            </div><br>

            <label for="bilan">Bilan <span style="color:red;">*</span> :</label>
            <textarea name="bilan" id="bilan" required maxlength="255" class="form-control"
                rows="4"><?php echo htmlspecialchars($carac[6]); ?></textarea>
            <small class="text-muted">Maximum 255 caractères</small><br><br>

            <label for="medoc1">Médicament présenté 1 (optionnel) :</label>
            <select name="medoc1" id="medoc1" class="form-select">
                <option value="">Aucun</option>
                <?php foreach ($medicaments as $medoc) {
                    $selected = ($medoc['MED_DEPOTLEGAL'] == $carac[14]) ? 'selected' : '';
                    echo '<option value="' . $medoc['MED_DEPOTLEGAL'] . '" ' . $selected . '>' . htmlspecialchars($medoc['MED_NOMCOMMERCIAL']) . '</option>';
                } ?>
            </select><br><br>

            <label for="medoc2">Médicament présenté 2 (optionnel) :</label>
            <select name="medoc2" id="medoc2" class="form-select">
                <option value="">Aucun</option>
                <?php foreach ($medicaments as $medoc) {
                    $selected = ($medoc['MED_DEPOTLEGAL'] == $carac[15]) ? 'selected' : '';
                    echo '<option value="' . $medoc['MED_DEPOTLEGAL'] . '" ' . $selected . '>' . htmlspecialchars($medoc['MED_NOMCOMMERCIAL']) . '</option>';
                } ?>
            </select><br><br>

            <label for="numRemplacant">Remplaçant (optionnel) :</label>
            <select name="numRemplacant" id="numRemplacant" class="form-select">
                <option value="">Aucun</option>
                <?php foreach ($praticiens as $praticien) {
                    $selected = ($praticien['PRA_NUM'] == $carac[11]) ? 'selected' : '';
                    echo '<option value="' . $praticien['PRA_NUM'] . '" ' . $selected . '>' . htmlspecialchars($praticien['PRA_NOM'] . ' ' . $praticien['PRA_PRENOM']) . '</option>';
                } ?>
            </select><br><br>

            <label for="etat">État du rapport <span style="color:red;">*</span> :</label>
            <select name="etat" id="etat" required class="form-select">
                <option value="1" selected>Nouveau</option>
                <option value="2">Clos</option>
            </select><br><br>

            <!-- Section Echantillons -->
            <hr class="my-4">
            <div class="mb-4">
                <label class="form-label">Échantillons Offerts</label>
                <div id="echantillons-container"></div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="ajouter-echantillon">
                    + Ajouter un échantillon
                </button>
                <div class="form-text">Max 10 produits différents</div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="index.php?uc=rapportvisite&action=voirrapport" class="btn btn-secondary">Annuler</a>
                <input class="btn btn-success" type="submit" value="Enregistrer les modifications">
            </div>
        </form>
    </div>
</div>

<!-- Template Échantillon -->
<template id="echantillon-template">
    <div class="echantillon-row row mb-2 g-2 align-items-end">
        <div class="col-12 col-sm-6">
            <label class="form-label small text-muted">Échantillon <span class="echantillon-index">1</span></label>
            <select name="echantillon_medoc_X" class="form-select form-select-sm echantillon-medoc">
                <option value="">Aucun</option>
                <?php foreach ($medicaments as $medoc) {
                    echo '<option value="' . $medoc['MED_DEPOTLEGAL'] . '">' . htmlspecialchars($medoc['MED_NOMCOMMERCIAL']) . '</option>';
                } ?>
            </select>
        </div>
        <div class="col-8 col-sm-4">
            <label class="form-label small text-muted">Quantité</label>
            <input type="number" name="echantillon_qte_X" min="1" max="99"
                class="form-control form-control-sm echantillon-qte">
        </div>
        <div class="col-4 col-sm-2 text-end">
            <button type="button" class="btn btn-danger btn-sm w-100 supprimer-echantillon">
                X
            </button>
        </div>
    </div>
</template>

<?php
$echantillonsDataJSON = isset($echantillonsInitiaux) ? json_encode($echantillonsInitiaux) : '[]';
?>

<script>
    // 1. Définition de la variable globale JS lisible par le script externe
    const ECHANTILLONS_INITIAUX = <?php echo $echantillonsDataJSON; ?>; 
</script>
<script src="assets/js/modifierRapportDeVisite.js"></script>