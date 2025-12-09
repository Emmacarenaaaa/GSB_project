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
        $visiteurFiltre = isset($_POST['visiteurFiltre']) && !empty($_POST['visiteurFiltre']) ? $_POST['visiteurFiltre'] : null;

        // Récupérer les infos de l'utilisateur connecté pour avoir sa région
        $infosUtilisateur = getAllInformationCompte($_SESSION['matricule']);
        $regionCode = $infosUtilisateur['reg_code'];
        $habId = $_SESSION['hab_id'];
        $secCode = isset($_SESSION['sec_code']) ? $_SESSION['sec_code'] : null;

        // Si Visiteur (1), on force le filtre sur son matricule
        if ($habId == 1) {
            $visiteurFiltre = $_SESSION['matricule'];
        }

        // Si Responsable (3), on filtre par secteur
        $secteurFiltre = null;
        if ($habId == 3) {
            $secteurFiltre = $secCode;
        }

        // Récupérer les rapports filtrés
        $result = getAllRapportDeVisite($dateDebut, $dateFin, $praticienFiltre, $regionCode, $visiteurFiltre, $secteurFiltre);

        // Récupérer la liste des praticiens pour le filtre
        $praticiens = getAllPraticiens();

        // Récupérer la liste des visiteurs pour le filtre
        $visiteurs = [];
        if ($habId == 2) { // Délégué Régional
            $visiteurs = getCollaborateursByRegion($regionCode);
        } elseif ($habId == 3 && $secCode) { // Responsable Secteur
            $visiteurs = getCollaborateursBySecteur($secCode);
        }

        include("vues/v_formulaireRapportsDeVisite.php");
        break;
    }

    case 'mesRapportsBrouillon': {
        $matricule = $_SESSION['matricule'];
        $lesRapportsBrouillon = getLesRapportsBrouillon($matricule);
        include("vues/v_rapportsBrouillon.php");
        break;
    }

    case 'afficherrapport': {
        if (isset($_REQUEST['rapports'])) {
            $rapNum = $_REQUEST['rapports'];
            $carac = getAllInformationRapportDeVisiteNum($rapNum);

            if ($carac === false) {
                $_SESSION['erreur'] = "Rapport introuvable.";
                header("Location: index.php?uc=rapportvisite&action=voirrapport");
                exit;
            }

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

        $mode = 'creation'; // Indicateur pour la vue
        include("vues/v_formulaireRapportDeVisite.php");
        break;
    }

    case 'enregistrerrapport': {
        try {
            // Récupérer les infos du formulaire (sauf matricule)
            $numPraticien = $_POST['praticien'];
            $dateVisite = $_POST['dateVisite'];
            $motif = $_POST['motif'];
            $bilan = $_POST['bilan'];

            $motifAutre = (isset($_POST['motif_autre']) && $motif == 4) ? trim($_POST['motif_autre']) : null;

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

            // Insertion
            $resultat = insertRapport($matricule, $numPraticien, $dateVisite, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $numRemplacant, $etat);

            if ($resultat) {
                $_SESSION['succes'] = 'Rapport bien enregistré !';
                header('Location: index.php?uc=rapportvisite&action=voirrapport');
                exit;
            } else {
                $_SESSION['erreur'] = 'Échec de l\'enregistrement du rapport.';
                header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                exit;
            }

        } catch (Exception $e) {
            $_SESSION['erreur'] = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
            header('Location: index.php?uc=rapportvisite&action=saisirrapport');
            exit;
        }
    }

    case 'editerrapport': {
        $rapNum = $_REQUEST['rapports'];
        $carac = getAllInformationRapportDeVisiteNum($rapNum);

        if ($carac === false) {
            $_SESSION['erreur'] = "Rapport introuvable.";
            header("Location: index.php?uc=rapportvisite&action=voirrapport");
            exit;
        }

        // Vérifier si le rapport est bien en état "Nouveau" (1)
        if ($carac[17] != 1) {
            $_SESSION['erreur'] = "Ce rapport n'est pas modifiable.";
            header("Location: index.php?uc=rapportvisite&action=voirrapport");
            exit;
        }

        $motifs = getMotifs();
        $praticiens = getAllPraticiens();
        $medicaments = getMedicaments();

        $mode = 'modification'; // Indicateur pour la vue
        include("vues/v_formulaireRapportDeVisite.php");
        break;
    }

    case 'sauvegarderModification': {
        try {
            $rapNum = $_POST['rapNum'];
            $motif = $_POST['motif'];
            $bilan = $_POST['bilan'];
            $etat = $_POST['etat'];

            $motifAutre = (isset($_POST['motif_autre']) && $motif == 4) ? trim($_POST['motif_autre']) : null;

            $medoc1 = !empty($_POST['medoc1']) ? $_POST['medoc1'] : null;
            $medoc2 = !empty($_POST['medoc2']) ? $_POST['medoc2'] : null;
            $numRemplacant = !empty($_POST['numRemplacant']) ? $_POST['numRemplacant'] : null;

            // Validation (similaire à l'insertion)
            if (strlen($bilan) > 255) {
                $_SESSION['erreur'] = 'Le bilan ne peut pas dépasser 255 caractères.';
                header("Location: index.php?uc=rapportvisite&action=editerrapport&rapports=$rapNum");
                exit;
            }

            if ($motif == 4 && empty($motifAutre)) {
                $_SESSION['erreur'] = 'Veuillez préciser le motif.';
                header("Location: index.php?uc=rapportvisite&action=editerrapport&rapports=$rapNum");
                exit;
            }

            $resultat = updateRapport($rapNum, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $numRemplacant, $etat);

            if ($resultat) {
                $_SESSION['succes'] = 'Rapport modifié avec succès !';
                header('Location: index.php?uc=rapportvisite&action=mesRapportsBrouillon');
                exit;
            } else {
                $_SESSION['erreur'] = 'Erreur lors de la modification.';
                header("Location: index.php?uc=rapportvisite&action=editerrapport&rapports=$rapNum");
                exit;
            }

        } catch (Exception $e) {
            $_SESSION['erreur'] = 'Erreur : ' . $e->getMessage();
            header("Location: index.php?uc=rapportvisite&action=editerrapport&rapports=$rapNum");
            exit;
        }
    }
}

?>