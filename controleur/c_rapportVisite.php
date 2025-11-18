<?php

if (!isset($_REQUEST['action']) || empty($_REQUEST['action'])) {
  $action = "voirrapport";
} else {
  $action = $_REQUEST['action'];
}
switch ($action) {
  case 'voirrapport': {

 $dateDebut = isset($_POST['dateDebut']) && !empty($_POST['dateDebut']) ? $_POST['dateDebut'] : null;
      $dateFin = isset($_POST['dateFin']) && !empty($_POST['dateFin']) ? $_POST['dateFin'] : null;
      $praticienFiltre = isset($_POST['praticienFiltre']) && !empty($_POST['praticienFiltre']) ? $_POST['praticienFiltre'] : null;
      
      // Récupérer les rapports filtrés
      $result = getAllRapportDeVisite($dateDebut, $dateFin, $praticienFiltre);
      
      // Récupérer la liste des praticiens pour le filtre
      $praticiens = getAllPraticiens();
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
    // BLOQUER l'accès pour HAB_id = 3
    if (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] == 3) {
        $_SESSION['erreur'] = 'Vous n\'avez pas les droits pour créer un rapport.';
        header('Location: index.php?uc=rapportvisite&action=voirrapport');
        exit;
    }
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
// Validation de la longueur du bilan
        if (strlen($bilan) > 255) {
            $_SESSION['erreur'] = 'Le bilan ne peut pas dépasser 255 caractères.';
            header('Location: index.php?uc=rapportvisite&action=saisirrapport');
            exit;
        }

        // Validation du motif autre
        $motifAutre = null;
        if ($motif == 4) {
            if (empty($_POST['motif_autre'])) {
                $_SESSION['erreur'] = 'Veuillez préciser le motif.';
                header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                exit;
            }
            $motifAutre = trim($_POST['motif_autre']);
            if (strlen($motifAutre) > 50) {
                $_SESSION['erreur'] = 'Le motif personnalisé ne peut pas dépasser 50 caractères.';
                header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                exit;
            }
        }

        // Validation du format de date
        $dateObj = DateTime::createFromFormat('Y-m-d', $dateVisite);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $dateVisite) {
            $_SESSION['erreur'] = 'Format de date invalide.';
            header('Location: index.php?uc=rapportvisite&action=saisirrapport');
            exit;
        }

        // Médocs et remplaçant optionnels
        $medoc1 = !empty($_POST['medoc1']) ? trim($_POST['medoc1']) : null;
        $medoc2 = !empty($_POST['medoc2']) ? trim($_POST['medoc2']) : null;
        $numRemplacant = !empty($_POST['numRemplacant']) ? intval($_POST['numRemplacant']) : null;
        $matricule = $_SESSION['matricule'];

        // LOG
        error_log("Tentative d'insertion rapport - matricule: $matricule, praticien: $numPraticien");

        // Insertion
        $resultat = insertRapport($matricule, $numPraticien, $dateVisite, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $numRemplacant, $etat);

        // Appelle la fonction insert en passant aussi le motifAutre 
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

$action = isset($_GET['action']) ? $_GET['action'] : 'default';
switch ($action) {
    case 'gerer':
        include('v_gererPraticien.php');
        break;
    // autres actions (afficherprat, etc.)
    default:
        include('v_formulairePraticien.php');
        break;
}


}
?>