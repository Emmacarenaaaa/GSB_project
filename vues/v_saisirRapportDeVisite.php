<div class="row align-items-center justify-content-center">
    <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5 py-3">
        <form id="formRapport" action="index.php?uc=rapportvisite&action=enregistrerrapport" method="post"
            class="formulaire-recherche col-12 m-0" onsubmit="return validerFormulaire();">
            <label for="praticien">Praticien <span style="color:red;">*</span> :</label>
            <select name="praticien" id="praticien" required class="form-select">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($praticiens as $praticien) {
                    $label = $praticien['PRA_NOM'] . ' ' . $praticien['PRA_PRENOM'];
                    if (!empty($praticien['REG_NOM'])) {
                        $label .= ' (' . $praticien['REG_NOM'] . ')';
                    }
                    echo '<option value="' . $praticien['PRA_NUM'] . '">' . htmlspecialchars($label) . '</option>';
                } ?>
            </select> <br><br>

            <label for="dateVisite">Date de visite <span style="color:red;">*</span> :</label>
            <input type="date" name="dateVisite" id="dateVisite" required class="form-control"><br><br>

            <label for="motif">Motif <span style="color:red;">*</span> :</label>
            <select name="motif" id="motif" required class="form-select" onchange="toggleMotifAutre()">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($motifs as $motif) {
                    $label = htmlspecialchars($motif['MO_LIBELLE']);
                    echo '<option value="' . $motif['MO_CODE'] . '">' . $label . '</option>';
                } ?>
            </select> <br><br>

            <div id="motifAutreContainer" style="display:none;">
                <label for="motif_autre">Précisez le motif <span style="color:red;">*</span> :</label>
                <input type="text" name="motif_autre" id="motif_autre" maxlength="50" class="form-control">
                <small class="text-muted">Maximum 50 caractères</small>
            </div><br>

            <label for="bilan">Bilan <span style="color:red;">*</span> :</label>
            <textarea name="bilan" id="bilan" required maxlength="255" class="form-control" rows="4"></textarea>
            <small class="text-muted">Maximum 255 caractères</small><br><br>

            <label for="medoc1">Médicament présenté 1 (optionnel) :</label>
            <select name="medoc1" id="medoc1" class="form-select">
                <option value="">Aucun</option>
                <?php foreach ($medicaments as $medoc) {
                    echo '<option value="' . $medoc['MED_DEPOTLEGAL'] . '">' . htmlspecialchars($medoc['MED_NOMCOMMERCIAL']) . '</option>';
                } ?>
            </select><br><br>

            <label for="medoc2">Médicament présenté 2 (optionnel) :</label>
            <select name="medoc2" id="medoc2" class="form-select">
                <option value="">Aucun</option>
                <?php foreach ($medicaments as $medoc) {
                    echo '<option value="' . $medoc['MED_DEPOTLEGAL'] . '">' . htmlspecialchars($medoc['MED_NOMCOMMERCIAL']) . '</option>';
                } ?>
            </select><br><br>

            <label for="numRemplacant">Remplaçant (optionnel) :</label>
            <select name="numRemplacant" id="numRemplacant" class="form-select">
                <option value="">Aucun</option>
                <?php foreach ($praticiens as $praticien) {
                    echo '<option value="' . $praticien['PRA_NUM'] . '">' . htmlspecialchars($praticien['PRA_NOM'] . ' ' . $praticien['PRA_PRENOM']) . '</option>';
                } ?>
            </select><br><br>

            <label for="etat">État du rapport <span style="color:red;">*</span> :</label>
            <select name="etat" id="etat" required class="form-select">
                <option value="1">Nouveau</option>
                <option value="2">Clos</option>
            </select><br><br>

            <input class="btn btn-success" type="submit" value="Enregistrer le rapport">
        </form>
    </div>
</div>

<script>
    function toggleMotifAutre() {
        var motifSelect = document.getElementById('motif');
        var container = document.getElementById('motifAutreContainer');
        var inputAutre = document.getElementById('motif_autre');

        if (motifSelect.value == '4') {
            container.style.display = 'block';
            inputAutre.required = true;
        } else {
            container.style.display = 'none';
            inputAutre.required = false;
            inputAutre.value = '';
        }
    }

    function validerFormulaire() {
        // Récupérer les valeurs
        var praticien = document.getElementById('praticien').value;
        var dateVisite = document.getElementById('dateVisite').value;
        var motif = document.getElementById('motif').value;
        var bilan = document.getElementById('bilan').value.trim();
        var etat = document.getElementById('etat').value;

        // Vérifier le champ "Motif autre" si motif = 4
        if (motif == '4') {
            var motifAutre = document.getElementById('motif_autre').value.trim();
            if (motifAutre === '') {
                alert('Veuillez préciser le motif.');
                document.getElementById('motif_autre').focus();
                return false;
            }
            if (motifAutre.length > 50) {
                alert('Le motif personnalisé ne peut pas dépasser 50 caractères.');
                document.getElementById('motif_autre').focus();
                return false;
            }
        }

        // Vérifications des champs obligatoires
        if (praticien === '') {
            alert('Veuillez sélectionner un praticien.');
            document.getElementById('praticien').focus();
            return false;
        }

        if (dateVisite === '') {
            alert('Veuillez saisir une date de visite.');
            document.getElementById('dateVisite').focus();
            return false;
        }

        if (motif === '') {
            alert('Veuillez sélectionner un motif.');
            document.getElementById('motif').focus();
            return false;
        }

        if (bilan === '') {
            alert('Veuillez remplir le bilan.');
            document.getElementById('bilan').focus();
            return false;
        }

        if (bilan.length > 255) {
            alert('Le bilan ne peut pas dépasser 255 caractères.');
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

        // Récupérer les textes pour l'affichage
        var praticienTexte = document.getElementById('praticien').options[document.getElementById('praticien').selectedIndex].text;
        var motifTexte = document.getElementById('motif').options[document.getElementById('motif').selectedIndex].text;
        var etatTexte = document.getElementById('etat').options[document.getElementById('etat').selectedIndex].text;

        // Message de confirmation détaillé
        var message = 'CONFIRMEZ-VOUS L\'ENREGISTREMENT DE CE RAPPORT ?\n\n';
        message += '=====================================\n';
        message += 'Praticien : ' + praticienTexte + '\n';
        message += 'Date de visite : ' + dateVisite + '\n';
        message += 'Motif : ' + motifTexte + '\n';

        if (motif == '4') {
            message += 'Precision : ' + document.getElementById('motif_autre').value + '\n';
        }

        var medoc1Select = document.getElementById('medoc1');
        if (medoc1Select.value !== '') {
            message += 'Medicament 1 : ' + medoc1Select.options[medoc1Select.selectedIndex].text + '\n';
        }

        var medoc2Select = document.getElementById('medoc2');
        if (medoc2Select.value !== '') {
            message += 'Medicament 2 : ' + medoc2Select.options[medoc2Select.selectedIndex].text + '\n';
        }

        var remplacantSelect = document.getElementById('numRemplacant');
        if (remplacantSelect.value !== '') {
            message += 'Remplacant : ' + remplacantSelect.options[remplacantSelect.selectedIndex].text + '\n';
        }

        message += 'Etat : ' + etatTexte + '\n';
        message += '=====================================\n\n';
        message += 'Cliquez sur OK pour enregistrer.';

        // Retourner true si confirmé, false sinon
        return confirm(message);
    }
</script>