<?php
if (!isset($_REQUEST['action']) || empty($_REQUEST['action'])) {
  $action = "voirrapport";
} else {
  $action = $_REQUEST['action'];
}
switch ($action) {
  case 'voirrapport': {


      $result = getAllrapportDeVisite();
      include("vues/v_formulaireRapportsDeVisite.php");
      break;
    }


  case 'afficherrapport': {


      if (isset($_REQUEST['rapports']) && getAllInformationRapportDeVisiteNum($_REQUEST['rapports'])) {
        $rapNum = $_REQUEST['rapports'];
        $carac = getAllInformationRapportDeVisiteNum($rapNum);
         if (empty($carac[11])) {
      $carac[11] = '';// numéro remplacant
      $carac[12] = ''; // nom remplacant
      $carac[13] = ''; // prénom remplacant
    }
        include("vues/v_afficherRapportDeVisite.php");
      } else {
        $_SESSION['erreur'] = true;
        header("Location: index.php?uc=rapportvisite&action=voirrapport");
        exit;
      }
      break;
    }
case 'saisirrapport': {
    // Pour afficher le formulaire de saisie
    $motifs = getMotifs();
    $praticiens = getAllPraticiens();
    $medicaments = getMedicaments(); 
    include("vues/v_saisirRapportDeVisite.php");
    break;
}

case 'enregistrerrapport': {
    session_start();
    // Récupérer les infos du formulaire (sauf matricule)
    $numPraticien = $_POST['praticien'];
    $dateVisite = $_POST['dateVisite'];
    $motif = $_POST['motif'];
    $bilan = $_POST['bilan'];

    // Motif "autre" selon la valeur du code
    $motifAutre = (isset($_POST['motif_autre']) && $motif == 4) ? trim($_POST['motif_autre']) : null;

    // Médocs et remplaçant optionnels
    $medoc1 = !empty($_POST['medoc1']) ? $_POST['medoc1'] : null;
    $medoc2 = !empty($_POST['medoc2']) ? $_POST['medoc2'] : null;
    $numpratremp = !empty($_POST['numRemplacant']) ? $_POST['numRemplacant'] : null;
    $etat = !empty($_POST['etat']) ? $_POST['etat'] : 0; 
    $matriculeVisiteur = $_SESSION['matricule'];

    if (addRapportDeVisite($numPraticien, $matriculeVisiteur, $dateVisite, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $etat, $numpratremp)) {
        $_SESSION['succes'] = 'Rapport bien enregistré !';
        header('Location: index.php?uc=rapportvisite&action=voirrapport');
        exit;
    } else {
        $_SESSION['erreur'] = 'Erreur lors de l\'enregistrement du rapport';
        header('Location: index.php?uc=rapportvisite&action=saisirrapport');
        exit;
    }
}}
?>