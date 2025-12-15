<?php
// Déterminer le mode (création ou modification)
$isModification = isset($mode) && $mode == 'modification';
$titre = $isModification ? "Modifier le rapport n°" . $rapNum : "Nouveau rapport de visite";
$actionForm = $isModification ? "index.php?uc=rapportvisite&action=sauvegarderModification" : "index.php?uc=rapportvisite&action=enregistrerrapport";

// Valeurs par défaut (ou récupérées si modification)
$praNumVal = $isModification ? $carac[8] : (isset($_POST['praticien']) ? $_POST['praticien'] : '');
$dateVisiteVal = $isModification ? $carac[4] : (isset($_POST['dateVisite']) ? $_POST['dateVisite'] : '');
// Pour le motif, en modification on a le LIBELLE dans $carac[5], pas le code. On essaiera de mapper ou on utilisera le code si dispo
$motifCodeVal = $isModification ? '' : (isset($_POST['motif']) ? $_POST['motif'] : '');
$motifAutreVal = $isModification ? ($carac[5] != 'Autre' && !in_array($carac[5], array_column($motifs, 'MO_LIBELLE')) ? $carac[5] : '') : '';
$bilanVal = $isModification ? $carac[6] : (isset($_POST['bilan']) ? $_POST['bilan'] : '');
$medoc1Val = $isModification ? $carac[14] : (isset($_POST['medoc1']) ? $_POST['medoc1'] : '');
$medoc2Val = $isModification ? $carac[15] : (isset($_POST['medoc2']) ? $_POST['medoc2'] : '');
$remplacantVal = $isModification ? $carac[11] : (isset($_POST['numRemplacant']) ? $_POST['numRemplacant'] : '');
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8 py-4">

        <h2 class="mb-4 text-center"><?php echo $titre; ?></h2>

        <form id="formRapport" action="<?php echo $actionForm; ?>" method="post" onsubmit="return validerFormulaire();"
            class="p-4 border rounded bg-white">

            <?php if ($isModification): ?>
                <input type="hidden" name="rapNum" value="<?php echo $rapNum; ?>">
            <?php endif; ?>

            <!-- Praticien -->
            <div class="mb-3">
                <label for="praticien" class="form-label">Praticien <span class="text-danger">*</span></label>
                <select name="praticien" id="praticien" required class="form-select" <?php echo $isModification ? 'disabled' : ''; ?>>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($praticiens as $praticien) {
                        $selected = ($praticien['PRA_NUM'] == $praNumVal) ? 'selected' : '';
                        $label = $praticien['PRA_NOM'] . ' ' . $praticien['PRA_PRENOM'];
                        if (!empty($praticien['REG_NOM'])) {
                            $label .= ' (' . $praticien['REG_NOM'] . ')';
                        }
                        echo '<option value="' . $praticien['PRA_NUM'] . '" ' . $selected . '>' . htmlspecialchars($label) . '</option>';
                    } ?>
                </select>
                <?php if ($isModification): ?>
                    <input type="hidden" name="praticien" value="<?php echo $praNumVal; ?>">
                <?php endif; ?>
            </div>

            <!-- Date de Visite -->
            <div class="mb-3">
                <label for="dateVisite" class="form-label">Date de visite <span class="text-danger">*</span></label>
                <input type="date" name="dateVisite" id="dateVisite" required class="form-control"
                    value="<?php echo htmlspecialchars($dateVisiteVal); ?>">
            </div>

            <!-- Motif -->
            <div class="mb-3">
                <label for="motif" class="form-label">Motif <span class="text-danger">*</span></label>
                <select name="motif" id="motif" required class="form-select" onchange="toggleMotifAutre()">
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($motifs as $motif) {
                        $selected = '';
                        if ($isModification) {
                            if ($motif['MO_LIBELLE'] == $carac[5]) {
                                $selected = 'selected';
                            }
                        } else {
                            if ($motif['MO_CODE'] == $motifCodeVal)
                                $selected = 'selected';
                        }
                        echo '<option value="' . $motif['MO_CODE'] . '" ' . $selected . '>' . htmlspecialchars($motif['MO_LIBELLE']) . '</option>';
                    } ?>
                </select>
            </div>

            <!-- Motif Autre -->
            <div class="mb-3" id="motifAutreContainer" style="display:none;">
                <label for="motif_autre" class="form-label">Précisez le motif <span class="text-danger">*</span></label>
                <input type="text" name="motif_autre" id="motif_autre" maxlength="50" class="form-control"
                    value="<?php echo isset($carac) && isset($carac[19]) ? htmlspecialchars($carac[19]) : ''; ?>">
                <div class="form-text">Maximum 50 caractères</div>
            </div>

            <!-- Bilan -->
            <div class="mb-3">
                <label for="bilan" class="form-label">Bilan <span class="text-danger">*</span></label>
                <textarea name="bilan" id="bilan" required maxlength="255" class="form-control"
                    rows="4"><?php echo htmlspecialchars($bilanVal); ?></textarea>
                <div class="form-text">Maximum 255 caractères</div>
            </div>

            <!-- Médicaments -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="medoc1" class="form-label">Médicament 1</label>
                    <select name="medoc1" id="medoc1" class="form-select">
                        <option value="">Aucun</option>
                        <?php foreach ($medicaments as $medoc) {
                            $selected = ($medoc['MED_DEPOTLEGAL'] == $medoc1Val) ? 'selected' : '';
                            echo '<option value="' . $medoc['MED_DEPOTLEGAL'] . '" ' . $selected . '>' . htmlspecialchars($medoc['MED_NOMCOMMERCIAL']) . '</option>';
                        } ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="medoc2" class="form-label">Médicament 2</label>
                    <select name="medoc2" id="medoc2" class="form-select">
                        <option value="">Aucun</option>
                        <?php foreach ($medicaments as $medoc) {
                            $selected = ($medoc['MED_DEPOTLEGAL'] == $medoc2Val) ? 'selected' : '';
                            echo '<option value="' . $medoc['MED_DEPOTLEGAL'] . '" ' . $selected . '>' . htmlspecialchars($medoc['MED_NOMCOMMERCIAL']) . '</option>';
                        } ?>
                    </select>
                </div>
            </div>

            <!-- Remplaçant -->
            <div class="mb-3">
                <label for="numRemplacant" class="form-label">Remplaçant (optionnel)</label>
                <select name="numRemplacant" id="numRemplacant" class="form-select">
                    <option value="">Aucun</option>
                    <?php foreach ($praticiens as $praticien) {
                        $selected = ($praticien['PRA_NUM'] == $remplacantVal) ? 'selected' : '';
                        $label = $praticien['PRA_NOM'] . ' ' . $praticien['PRA_PRENOM'];
                        if (!empty($praticien['REG_NOM'])) {
                            $label .= ' (' . $praticien['REG_NOM'] . ')';
                        }
                        echo '<option value="' . $praticien['PRA_NUM'] . '" ' . $selected . '>' . htmlspecialchars($label) . '</option>';
                    } ?>
                </select>
            </div>

            <hr class="my-4">

            <!-- Échantillons -->
            <div class="mb-4">
                <label class="form-label">Échantillons Offerts</label>

                <div id="echantillons-container"></div>

                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="ajouter-echantillon">
                    + Ajouter un échantillon
                </button>
                <div class="form-text">Max 10 produits différents</div>
            </div>

            <!-- Bouton Submit -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary py-2">
                    Enregistrer le rapport
                </button>
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

<script>
    function toggleMotifAutre() {
        var motifSelect = document.getElementById('motif');
        var container = document.getElementById('motifAutreContainer');
        var inputAutre = document.getElementById('motif_autre');
        var selectedText = motifSelect.options[motifSelect.selectedIndex].text;

        if (motifSelect.value == '4' || selectedText === 'Autre') {
            container.style.display = 'block';
            inputAutre.required = true;
        } else {
            container.style.display = 'none';
            inputAutre.required = false;
        }
    }

    function validerFormulaire() {
        return confirm('Confirmez-vous l\'enregistrement ?');
    }

    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Logique du Motif Autre ---
        toggleMotifAutre();

        // --- 2. Logique des Échantillons Dynamiques ---
        const container = document.getElementById('echantillons-container');
        const template = document.getElementById('echantillon-template');
        const addButton = document.getElementById('ajouter-echantillon');
        const MAX_ECHANTILLONS = 10;
        let echantillonCount = 0;

        function reindexEchantillons() {
            echantillonCount = 0;
            const rows = container.querySelectorAll('.echantillon-row');
            rows.forEach((row, index) => {
                const newIndex = index + 1;
                echantillonCount = newIndex;
                row.querySelector('.echantillon-index').textContent = newIndex;
                row.querySelector('.echantillon-medoc').name = `echantillon_medoc_${newIndex}`;
                row.querySelector('.echantillon-qte').name = `echantillon_qte_${newIndex}`;

                const deleteButton = row.querySelector('.supprimer-echantillon');
                deleteButton.style.display = (rows.length > 1) ? 'block' : 'none';
            });
            addButton.disabled = (echantillonCount >= MAX_ECHANTILLONS);
        }

        function addEchantillonRow() {
            if (echantillonCount >= MAX_ECHANTILLONS) return;
            const clone = template.content.cloneNode(true).querySelector('.echantillon-row');

            clone.querySelector('.supprimer-echantillon').addEventListener('click', function () {
                clone.remove();
                reindexEchantillons();
            });

            container.appendChild(clone);
            reindexEchantillons();
        }

        addButton.addEventListener('click', addEchantillonRow);

        if (echantillonCount === 0) {
            addEchantillonRow();
        }
    });
</script>