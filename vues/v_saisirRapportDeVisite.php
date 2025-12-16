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

<script src="assets/js/saisirRapportDeVisite.js"></script>