<?php
// Déterminer le mode (création ou modification)
$isModification = isset($mode) && $mode == 'modification';
$titre = $isModification ? "Modifier le rapport n°" . $rapNum : "Nouveau rapport de visite";
$actionForm = $isModification ? "index.php?uc=rapportvisite&action=sauvegarderModification" : "index.php?uc=rapportvisite&action=enregistrerrapport";

// Valeurs par défaut (ou récupérées si modification)
$praNumVal = $isModification ? $carac[8] : (isset($_POST['praticien']) ? $_POST['praticien'] : '');
$dateVisiteVal = $isModification ? $carac[4] : (isset($_POST['dateVisite']) ? $_POST['dateVisite'] : '');
// Pour le motif, en modification on a le LIBELLE dans $carac[5], pas le code. On essaiera de mapper ou on utilisera le code si dispo (TODO: adapter le modèle pour renvoyer le code)
// En attendant, pour la création on utilise '';
$motifCodeVal = $isModification ? '' : (isset($_POST['motif']) ? $_POST['motif'] : ''); // Compliqué pour modif sans le code
$motifAutreVal = $isModification ? ($carac[5] != 'Autre' && !in_array($carac[5], array_column($motifs, 'MO_LIBELLE')) ? $carac[5] : '') : ''; // Logique approximative pour l'instant
$bilanVal = $isModification ? $carac[6] : (isset($_POST['bilan']) ? $_POST['bilan'] : '');
$medoc1Val = $isModification ? $carac[14] : (isset($_POST['medoc1']) ? $_POST['medoc1'] : '');
$medoc2Val = $isModification ? $carac[15] : (isset($_POST['medoc2']) ? $_POST['medoc2'] : '');
$remplacantVal = $isModification ? $carac[11] : (isset($_POST['numRemplacant']) ? $_POST['numRemplacant'] : '');
$etatVal = $isModification ? 0 : 0; // Par défaut Nouveau
// NOTE: En modification, l'état est souvent géré à part ou fix, ici on laisse par défaut.
?>

<div class="row align-items-center justify-content-center">
    <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5 py-3">
        <form id="formRapport" action="<?php echo $actionForm; ?>" method="post" class="formulaire-recherche col-12 m-0"
            onsubmit="return validerFormulaire();">
            <h2 class="text-center mb-4"><?php echo $titre; ?></h2>

            <?php if ($isModification): ?>
                <input type="hidden" name="rapNum" value="<?php echo $rapNum; ?>">
            <?php endif; ?>

            <label for="praticien">Praticien <span style="color:red;">*</span> :</label>
            <select name="praticien" id="praticien" required class="form-select" <?php echo $isModification ? 'disabled' : ''; // Peut-être disabled en modif ? ?>>
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
            <?php if ($isModification): // Si disabled, il faut envoyer la valeur quand même ?>
                <input type="hidden" name="praticien" value="<?php echo $praNumVal; ?>">
            <?php endif; ?>
            <br><br>

            <label for="dateVisite">Date de visite <span style="color:red;">*</span> :</label>
            <input type="date" name="dateVisite" id="dateVisite" required class="form-control"
                value="<?php echo htmlspecialchars($dateVisiteVal); ?>"><br><br>

            <label for="motif">Motif <span style="color:red;">*</span> :</label>
            <select name="motif" id="motif" required class="form-select" onchange="toggleMotifAutre()">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($motifs as $motif) {
                    // Logique de sélection pour la modification (si on a le libellé)
                    $selected = '';
                    if ($isModification) {
                        if ($motif['MO_LIBELLE'] == $carac[5]) { // Comparaison par libellé car le modèle renvoie le libellé
                            $selected = 'selected';
                        }
                    } else {
                        if ($motif['MO_CODE'] == $motifCodeVal)
                            $selected = 'selected';
                    }

                    echo '<option value="' . $motif['MO_CODE'] . '" ' . $selected . '>' . htmlspecialchars($motif['MO_LIBELLE']) . '</option>';
                } ?>
            </select> <br><br>

            <div id="motifAutreContainer" style="display:none;">
                <label for="motif_autre">Précisez le motif <span style="color:red;">*</span> :</label>
                <input type="text" name="motif_autre" id="motif_autre" maxlength="50" class="form-control"
                    value="<?php echo isset($carac) && isset($carac[19]) ? htmlspecialchars($carac[19]) : ''; // Utiliser index correct pour motif autre si dispo ?>">
                <small class="text-muted">Maximum 50 caractères</small>
            </div><br>

            <label for="bilan">Bilan <span style="color:red;">*</span> :</label>
            <textarea name="bilan" id="bilan" required maxlength="255" class="form-control"
                rows="4"><?php echo htmlspecialchars($bilanVal); ?></textarea>
            <small class="text-muted">Maximum 255 caractères</small><br><br>

            <label for="medoc1">Médicament présenté 1 (optionnel) :</label>
            <select name="medoc1" id="medoc1" class="form-select">
                <option value="">Aucun</option>
                <?php foreach ($medicaments as $medoc) {
                    $selected = ($medoc['MED_DEPOTLEGAL'] == $medoc1Val) ? 'selected' : '';
                    echo '<option value="' . $medoc['MED_DEPOTLEGAL'] . '" ' . $selected . '>' . htmlspecialchars($medoc['MED_NOMCOMMERCIAL']) . '</option>';
                } ?>
            </select><br><br>

            <label for="medoc2">Médicament présenté 2 (optionnel) :</label>
            <select name="medoc2" id="medoc2" class="form-select">
                <option value="">Aucun</option>
                <?php foreach ($medicaments as $medoc) {
                    $selected = ($medoc['MED_DEPOTLEGAL'] == $medoc2Val) ? 'selected' : '';
                    echo '<option value="' . $medoc['MED_DEPOTLEGAL'] . '" ' . $selected . '>' . htmlspecialchars($medoc['MED_NOMCOMMERCIAL']) . '</option>';
                } ?>
            </select><br><br>

            <label for="numRemplacant">Remplaçant (optionnel) :</label>
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
            </select><br><br>

            <label for="etat">État du rapport <span style="color:red;">*</span> :</label>
            <select name="etat" id="etat" required class="form-select">
                <option value="0" <?php echo ($etatVal == 0) ? 'selected' : ''; ?>>Nouveau</option>
                <option value="1" <?php echo ($etatVal == 1) ? 'selected' : ''; ?>>Clos</option>
            </select><br><br>

            <div class="d-flex justify-content-between">
                <?php if ($isModification): ?>
                    <a href="index.php?uc=rapportvisite&action=voirrapport" class="btn btn-secondary">Annuler</a>
                    <input class="btn btn-info text-light valider" type="submit" value="Enregistrer les modifications">
                <?php else: ?>
                    <input class="btn btn-info text-light valider" type="submit" value="Enregistrer le rapport">
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleMotifAutre() {
        var motifSelect = document.getElementById('motif');
        var container = document.getElementById('motifAutreContainer');
        var inputAutre = document.getElementById('motif_autre');

        // On suppose que le code 4 correspond à "Autre". A verifier ou rendre dynamique.
        // Si on est en modification, on peut avoir le texte "Autre" sélectionné si on a matché par libellé.
        var selectedText = motifSelect.options[motifSelect.selectedIndex].text;

        if (motifSelect.value == '4' || selectedText === 'Autre') {
            container.style.display = 'block';
            inputAutre.required = true;
        } else {
            container.style.display = 'none';
            inputAutre.required = false;
            // inputAutre.value = ''; // On ne vide pas forcement si on change d'avis
        }
    }

    // Initialiser l'affichage du motif autre au chargement
    document.addEventListener('DOMContentLoaded', function () {
        toggleMotifAutre();
    });

    function validerFormulaire() {
        // Récupérer les valeurs
        var praticien = document.getElementById('praticien').value;
        var dateVisite = document.getElementById('dateVisite').value;
        var motif = document.getElementById('motif').value;
        var bilan = document.getElementById('bilan').value.trim();

        // Vérifications JS standard...
        // ... (reprendre la logique existante)

        // Vérifier le champ "Motif autre"
        var motifSelect = document.getElementById('motif');
        var selectedText = motifSelect.options[motifSelect.selectedIndex].text;

        if (motifSelect.value == '4' || selectedText === 'Autre') {
            var motifAutre = document.getElementById('motif_autre').value.trim();
            if (motifAutre === '') {
                alert('Veuillez préciser le motif.');
                document.getElementById('motif_autre').focus();
                return false;
            }
        }

        if (bilan === '') {
            alert('Veuillez remplir le bilan.');
            document.getElementById('bilan').focus();
            return false;
        }

        // BLOCAGE STRICT : La date ne peut PAS être dans le futur
        var dateSelectionnee = new Date(dateVisite);
        var aujourdhui = new Date();
        aujourdhui.setHours(0, 0, 0, 0);

        if (dateSelectionnee > aujourdhui) {
            alert('ERREUR : La date de visite ne peut pas être dans le futur.\nVeuillez sélectionner une date antérieure ou égale à aujourd\'hui.');
            document.getElementById('dateVisite').focus();
            return false;
        }

        return confirm('Confirmez-vous l\'enregistrement ?');
    }
</script>