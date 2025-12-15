<div class="row align-items-center justify-content-center">
 <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5 py-3">
<form id="formRapport" action="index.php?uc=rapportvisite&action=sauvegarderModification" method="post" class="formulaire-recherche col-12 m-0" onsubmit="return validerFormulaire();">
 <h2 class="text-center mb-4">Modifier le rapport n°<?php echo $rapNum; ?></h2>
 
 <input type="hidden" name="rapNum" value="<?php echo $rapNum; ?>">
 
 <label for="praticien">Praticien <span style="color:red;">*</span> :</label>
 <select name="praticien" id="praticien" class="form-select" required>
  <?php foreach($praticiens as $praticien) {
   $selected = ($praticien['PRA_NUM'] == $carac[8]) ? 'selected' : '';
   echo '<option value="'.$praticien['PRA_NUM'].'" '.$selected.'>'.htmlspecialchars($praticien['PRA_NOM'].' '.$praticien['PRA_PRENOM']).'</option>';
  } ?>
 </select>
 <br>

 <label for="dateVisite">Date de visite <span style="color:red;">*</span> :</label>
 <input type="date" name="dateVisite" id="dateVisite" class="form-control" value="<?php echo htmlspecialchars($carac[4]); ?>" required>
 <br>

 <label for="motif">Motif <span style="color:red;">*</span> :</label>
 <select name="motif" id="motif" required class="form-select" onchange="toggleMotifAutre()">
  <option value="">-- Sélectionner --</option>
  <?php foreach($motifs as $motif) {
   $selected = '';
   // Si le libellé correspond
   if ($motif['MO_LIBELLE'] == $carac[5]) {
       $selected = 'selected';
   }
   
   echo '<option value="'.$motif['MO_CODE'].'" '.$selected.'>'.htmlspecialchars($motif['MO_LIBELLE']).'</option>';
  } ?>
 </select> <br><br>

 <div id="motifAutreContainer" style="display:none;">
  <label for="motif_autre">Précisez le motif <span style="color:red;">*</span> :</label>
  <input type="text" name="motif_autre" id="motif_autre" maxlength="50" class="form-control" value="<?php echo ($carac[5] != 'Autre' && !in_array($carac[5], array_column($motifs, 'MO_LIBELLE'))) ? htmlspecialchars($carac[5]) : ''; ?>">
  <small class="text-muted">Maximum 50 caractères</small>
 </div><br>

 <label for="bilan">Bilan <span style="color:red;">*</span> :</label>
 <textarea name="bilan" id="bilan" required maxlength="255" class="form-control" rows="4"><?php echo htmlspecialchars($carac[6]); ?></textarea>
 <small class="text-muted">Maximum 255 caractères</small><br><br>

 <label for="medoc1">Médicament présenté 1 (optionnel) :</label>
 <select name="medoc1" id="medoc1" class="form-select">
  <option value="">Aucun</option>
  <?php foreach($medicaments as $medoc) {
   $selected = ($medoc['MED_DEPOTLEGAL'] == $carac[14]) ? 'selected' : '';
   echo '<option value="'.$medoc['MED_DEPOTLEGAL'].'" '.$selected.'>'.htmlspecialchars($medoc['MED_NOMCOMMERCIAL']).'</option>';
  } ?>
 </select><br><br>

 <label for="medoc2">Médicament présenté 2 (optionnel) :</label>
 <select name="medoc2" id="medoc2" class="form-select">
  <option value="">Aucun</option>
  <?php foreach($medicaments as $medoc) {
   $selected = ($medoc['MED_DEPOTLEGAL'] == $carac[15]) ? 'selected' : '';
   echo '<option value="'.$medoc['MED_DEPOTLEGAL'].'" '.$selected.'>'.htmlspecialchars($medoc['MED_NOMCOMMERCIAL']).'</option>';
  } ?>
 </select><br><br>

 <label for="numRemplacant">Remplaçant (optionnel) :</label>
 <select name="numRemplacant" id="numRemplacant" class="form-select">
  <option value="">Aucun</option>
  <?php foreach($praticiens as $praticien) {
   $selected = ($praticien['PRA_NUM'] == $carac[11]) ? 'selected' : '';
   echo '<option value="'.$praticien['PRA_NUM'].'" '.$selected.'>'.htmlspecialchars($praticien['PRA_NOM'].' '.$praticien['PRA_PRENOM']).'</option>';
  } ?>
 </select><br><br>

 <label for="etat">État du rapport <span style="color:red;">*</span> :</label>
 <select name="etat" id="etat" required class="form-select">
  <option value="1" selected>Nouveau</option>
  <option value="2">Clos</option>
 </select><br><br>

 <div class="d-flex justify-content-between">
     <a href="index.php?uc=rapportvisite&action=voirrapport" class="btn btn-secondary">Annuler</a>
     <input class="btn btn-success" type="submit" value="Enregistrer les modifications">
 </div>
</form>
</div>
</div>

<script>
function toggleMotifAutre() {
 var motifSelect = document.getElementById('motif');
 var container = document.getElementById('motifAutreContainer');
 var inputAutre = document.getElementById('motif_autre');
 
 if (motifSelect.value == '4') { // 4 est supposé être le code pour "Autre" ou similaire, à vérifier avec la BDD
  container.style.display = 'block';
  inputAutre.required = true;
 } else {
  container.style.display = 'none';
  inputAutre.required = false;
 }
}

// Initialiser l'affichage du motif autre au chargement
document.addEventListener('DOMContentLoaded', function() {
    toggleMotifAutre();
});

function validerFormulaire() {
 // Récupérer les valeurs
 var motif = document.getElementById('motif').value;
 var bilan = document.getElementById('bilan').value.trim();
 
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
 
 return confirm('Confirmez-vous la modification de ce rapport ?');
}
</script>
