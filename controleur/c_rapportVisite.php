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
    try {
        // LOG : Vérifier les données reçues
        error_log("POST data: " . print_r($_POST, true));
        error_log("Session matricule: " . $_SESSION['matricule']);

    // Récupérer les infos du formulaire (sauf matricule)
    $numPraticien = $_POST['praticien'];
    $dateVisite = $_POST['dateVisite'];
    $motif = $_POST['motif'];
    $bilan = $_POST['bilan'];

    $motifAutre = (isset($_POST['motif_autre']) && $motif == 4) ? trim($_POST['motif_autre']) :null; 

        // Médocs et remplaçant optionnels
        $medoc1 = !empty($_POST['medoc1']) ? $_POST['medoc1'] : null;
        $medoc2 = !empty($_POST['medoc2']) ? $_POST['medoc2'] : null;
        $numRemplacant = !empty($_POST['numRemplacant']) ? $_POST['numRemplacant'] : null;
        $etat = !empty($_POST['etat']) ? $_POST['etat'] : 0;
        $matricule = $_SESSION['matricule'];
          // LOG : Avant l'insertion
        error_log("Avant insertRapport avec matricule: $matricule, praticien: $numPraticien");
  $resultat = insertRapport($matricule, $numPraticien, $dateVisite, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $numRemplacant , $etat);
        
        // LOG : Résultat
        error_log("Résultat insertRapport: " . ($resultat ? 'true' : 'false'));

        // Appelle la fonction insert en passant aussi le motifAutre !
        if ($resultat) {
            $_SESSION['succes'] = 'Rapport bien enregistré !';
            header('Location: index.php?uc=rapportvisite&action=voirrapport');
            exit;} else {
                 error_log("insertRapport a retourné false");
            // Gérer le cas où insertRapport retourne false
            $_SESSION['erreur'] = 'Échec de l\'enregistrement du rapport.';
            header('Location: index.php?uc=rapportvisite&action=saisirrapport');
            exit;
        }
        
    } catch (Exception $e) {
        $_SESSION['erreur'] = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
        header('Location: index.php?uc=rapportvisite&action=remplirrapport');
        exit;
    }
}
}
?>