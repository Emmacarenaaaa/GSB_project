

<form action="index.php?uc=rapportvisite&action=enregistrerrapport" method="post" class="form-group">
 <label for="praticien">Praticien :</label>
 <select name="praticien" required class="form-select">
  <option value="">-- Sélectionner --</option>
  <?php foreach($praticiens as $praticien) {
   echo '<option value="'.$praticien['PRA_NUM'].'">'.htmlspecialchars($praticien['PRA_NOM'].' '.$praticien['PRA_PRENOM']).'</option>';
  } ?>
 </select> <br><br>

 <label for="dateVisite">Date de visite :</label>
 <input type="date" name="dateVisite" required class="form-control"><br><br>

 <label for="motif">Motif :</label>
 <select name="motif" id="motif" required class="form-select" onchange="toggleMotifAutre(this)">
  <option value="">-- Sélectionner --</option>
  <?php foreach($motifs as $motif) {
   $label = htmlspecialchars($motif['MO_LIBELLE']);
   echo '<option value="'.$motif['MO_CODE'].'">'.$label.'</option>';
  } ?>
 </select> <br><br>

 <div id="motifAutreContainer" style="display:none;">
  <label for="motif_autre">Précisez le motif :</label>
  <input type="text" name="motif_autre" class="form-control">
 </div><br>

 <label for="bilan">Bilan :</label>
 <textarea name="bilan" required class="form-control"></textarea><br><br>

 <label for="medoc1">Médicament présenté 1 (optionnel) :</label>
 <select name="medoc1" class="form-select">
  <option value="">Aucun</option>
  <?php foreach($medicaments as $medoc) {
   echo '<option value="'.$medoc['MED_DEPOTLEGAL'].'">'.htmlspecialchars($medoc['MED_NOMCOMMERCIAL']).'</option>';
  } ?>
 </select><br><br>

 <label for="medoc2">Médicament présenté 2 (optionnel) :</label>
 <select name="medoc2" class="form-select">
  <option value="">Aucun</option>
  <?php foreach($medicaments as $medoc) {
   echo '<option value="'.$medoc['MED_DEPOTLEGAL'].'">'.htmlspecialchars($medoc['MED_NOMCOMMERCIAL']).'</option>';
  } ?>
 </select><br><br>

 <label for="numRemplacant">Remplaçant (optionnel) :</label>
 <select name="numRemplacant" class="form-select">
  <option value="">Aucun</option>
  <?php foreach($praticiens as $praticien) {
   echo '<option value="'.$praticien['PRA_NUM'].'">'.htmlspecialchars($praticien['PRA_NOM'].' '.$praticien['PRA_PRENOM']).'</option>';
  } ?>
 </select><br><br>

 <label for="etat">État du rapport :</label>
 <select name="etat" class="form-select">
  <option value="1">Nouveau</option>
  <option value="2">Clos</option>
 </select><br><br>

 <input class="btn btn-success" type="submit" value="Enregistrer le rapport">
</form>

<script>
function toggleMotifAutre(select) {
 var container = document.getElementById('motifAutreContainer');
 container.style.display = (select.value == '4') ? '' : 'none';
}
</script>
