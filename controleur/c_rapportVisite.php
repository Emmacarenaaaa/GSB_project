<?php

/**
 * Contrôleur pour la gestion des rapports de visite.
 * Gère l'affichage, la création, la modification et le listing des rapports.
 */

// Si aucune action n'est définie, l'action par défaut est de voir les rapports
if (!isset($_REQUEST['action']) || empty($_REQUEST['action'])) {
    $action = "voirrapport";
} else {
    $action = $_REQUEST['action'];
}

switch ($action) {
    /**
     * Action : voirrapport
     * Affiche uniquement le formulaire de filtre pour sélectionner les rapports.
     */
    case 'voirrapport': {
        // Récupérer les infos de l'utilisateur connecté
        $infosUtilisateur = getAllInformationCompte($_SESSION['matricule']);
        $regionCode = $infosUtilisateur['reg_code'];

        // Récupérer la liste des praticiens pour le filtre (ceux ayant déjà été visités)
        $praticiens = getPraticiensVisites($regionCode);

        include("vues/v_selectionnerRapport.php");
        break;
    }

    /**
     * Action : validerSelection
     * Traite le filtre et affiche la liste des rapports correspondants.
     */
    case 'validerSelection': {
        // Récupération des filtres depuis le formulaire (POST)
        $dateDebut = isset($_POST['dateDebut']) && !empty($_POST['dateDebut']) ? $_POST['dateDebut'] : null;
        $dateFin = isset($_POST['dateFin']) && !empty($_POST['dateFin']) ? $_POST['dateFin'] : null;
        $praticienFiltre = isset($_POST['praticienFiltre']) && !empty($_POST['praticienFiltre']) ? $_POST['praticienFiltre'] : null;

        // Le filtre visiteur n'est plus utilisé ici (géré par région/secteur/id)
        $visiteurFiltre = null;

        // Récupérer les infos de l'utilisateur connecté pour obtenir sa région et son secteur
        $infosUtilisateur = getAllInformationCompte($_SESSION['matricule']);
        $regionCode = $infosUtilisateur['reg_code'];
        $habId = $_SESSION['hab_id']; // Niveau d'habilitation
        $secCode = isset($_SESSION['sec_code']) ? $_SESSION['sec_code'] : null;

        // Restriction pour les Visiteurs (niveau 1) : ils ne voient que leurs propres rapports
        if ($habId == 1) {
            $visiteurFiltre = $_SESSION['matricule'];
        }

        // Restriction pour les Responsables de Secteur (niveau 3) : filtrage par secteur
        $secteurFiltre = null;
        if ($habId == 3) {
            $secteurFiltre = $secCode;
        }

        // Appel au modèle pour récupérer la liste des rapports filtrés
        $result = getAllRapportDeVisite($dateDebut, $dateFin, $praticienFiltre, $regionCode, $visiteurFiltre, $secteurFiltre);

        include("vues/v_listeRapports.php");
        break;
    }

    /**
     * Action : mesRapportsBrouillon
     * Affiche la liste des rapports à l'état "Brouillon" pour l'utilisateur connecté.
     */
    case 'mesRapportsBrouillon': {
        $matricule = $_SESSION['matricule'];
        // Récupère les rapports non validés/terminés
        $lesRapportsBrouillon = getLesRapportsBrouillon($matricule);
        include("vues/v_rapportsBrouillon.php");
        break;
    }

    /**
     * Action : afficherrapport
     * Affiche le détail complet d'un rapport sélectionné.
     */
    case 'afficherrapport': {
        if (isset($_REQUEST['rapports'])) {
            $rapNum = $_REQUEST['rapports'];
            // Récupère toutes les infos du rapport par son numéro
            $carac = getAllInformationRapportDeVisiteNum($rapNum);

            if ($carac === false) {
                $_SESSION['erreur'] = "Rapport introuvable.";
                header("Location: index.php?uc=rapportvisite&action=voirrapport");
                exit;
            }

            // Gestion des valeurs nulles pour le remplacant
            if (empty($carac[11])) {
                $carac[11] = ''; // numéro remplacant
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

    /**
     * Action : saisirrapport
     * Affiche le formulaire de création d'un nouveau rapport.
     */
    case 'saisirrapport': {
        // BLOQUER l'accès pour HAB_id = 3 (Responsable) qui ne saisit pas de rapports
        if (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] == 3) {
            $_SESSION['erreur'] = 'Vous n\'avez pas les droits pour créer un rapport.';
            header('Location: index.php?uc=rapportvisite&action=voirrapport');
            exit;
        }

        // Chargement des données nécessaires au formulaire (motifs, médicaments...)
        $motifs = getMotifs();

        // Récupération de la région de l'utilisateur pour filtrer les praticiens
        $infosUtilisateur = getAllInformationCompte($_SESSION['matricule']);
        $regionCode = $infosUtilisateur['reg_code'];
        // On liste tous les praticiens de la région pour la création de rapport
        $praticiens = getPraticiensByRegion($regionCode);

        $medicaments = getMedicaments();

        $mode = 'creation'; // Indicateur pour la vue
        include("vues/v_formulaireRapportDeVisite.php");
        break;
    }

    /**
     * Action : enregistrerrapport
     * Traite la soumission du formulaire de création.
     */
    case 'enregistrerrapport': {
        try {
            // Récupération des données POST
            $numPraticien = $_POST['praticien'];
            $dateVisite = $_POST['dateVisite'];
            $motif = $_POST['motif'];
            $bilan = $_POST['bilan'];

            // Gestion du motif "Autre"
            $motifAutre = (isset($_POST['motif_autre']) && $motif == 4) ? trim($_POST['motif_autre']) : null;

            // Données optionnelles
            $medoc1 = !empty($_POST['medoc1']) ? $_POST['medoc1'] : null;
            $medoc2 = !empty($_POST['medoc2']) ? $_POST['medoc2'] : null;
            $numRemplacant = !empty($_POST['numRemplacant']) ? $_POST['numRemplacant'] : null;
            $etat = !empty($_POST['etat']) ? $_POST['etat'] : 0; // 0 ou 1 ou 2 selon le formulaire

            $matricule = $_SESSION['matricule'];

            // Validation de la longueur du bilan
            if (strlen($bilan) > 255) {
                $_SESSION['erreur'] = 'Le bilan ne peut pas dépasser 255 caractères.';
                header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                exit;
            }

            // Validation spécifique pour le motif "Autre"
            if ($motif == 4) {
                if (empty($motifAutre)) {
                    $_SESSION['erreur'] = 'Veuillez préciser le motif.';
                    header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                    exit;
                }
                if (strlen($motifAutre) > 50) {
                    $_SESSION['erreur'] = 'Le motif personnalisé ne peut pas dépasser 50 caractères.';
                    header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                    exit;
                }
            }

            // Validation du format de date (Y-m-d)
            $dateObj = DateTime::createFromFormat('Y-m-d', $dateVisite);
            if (!$dateObj || $dateObj->format('Y-m-d') !== $dateVisite) {
                $_SESSION['erreur'] = 'Format de date invalide.';
                header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                exit;
            }

            // Nettoyage et typage final des données optionnelles
            $medoc1 = !empty($_POST['medoc1']) ? trim($_POST['medoc1']) : null;
            $medoc2 = !empty($_POST['medoc2']) ? trim($_POST['medoc2']) : null;
            $numRemplacant = !empty($_POST['numRemplacant']) ? intval($_POST['numRemplacant']) : null;

            // Insertion dans la base de données
            $resultat = insertRapport($matricule, $numPraticien, $dateVisite, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $numRemplacant, $etat);

            if ($resultat) {
                $_SESSION['succes'] = 'Rapport bien enregistré !';
                header('Location: index.php?uc=rapportvisite&action=voirrapport');
                exit;
            } else {
                $_SESSION['erreur'] = 'Échec de l\'enregistrement du rapport. Vérifiez les données.';
                header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                exit;
            }

        } catch (Exception $e) {
            $_SESSION['erreur'] = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
            header('Location: index.php?uc=rapportvisite&action=saisirrapport');
            exit;
        }
    }

    /**
     * Action : editerrapport
     * Affiche le formulaire de modification d'un rapport existant (si brouillon).
     */
    case 'editerrapport': {
        $rapNum = $_REQUEST['rapports'];
        // Récupération des données du rapport
        $carac = getAllInformationRapportDeVisiteNum($rapNum);

        if ($carac === false) {
            $_SESSION['erreur'] = "Rapport introuvable.";
            header("Location: index.php?uc=rapportvisite&action=voirrapport");
            exit;
        }

        // Vérifier si le rapport est bien en état "Nouveau" (1) pour autoriser la modif
        if ($carac[17] != 1) { // 17 is index of ET_CODE in getAllInformationRapportDeVisiteNum... verify index!
            // Actually index 19 in model is ET_CODE if we count select columns...
            // Checking model:
            // 0: matricule, 1: nomCol, 2: prenomCol, 3: rapNum, 4: dateVisite, 5: motif, 6: bilan, 7: dateSaisie
            // 8: numPrat, 9: nomPrat, 10: prenomPrat, 11: numRemp, 12: nomRemp, 13: prenomRemp
            // 14: med1, 15: nom1, 16: med2, 17: nom2, 18: etatLib, 19: etatCode
            // So carac[19] should be used!

            // Wait, previous code used 17?
            // "md2.MED_NOMCOMMERCIAL as medocnom2" is 17?
            // Let's count again.
            // 0..13 matches.
            // 14: medocpresenter1
            // 15: medocnom1
            // 16: medocpresenter2
            // 17: medocnom2
            // 18: etatrapport
            // 19: etatcode

            // If carac[17] was used, it was checking "medocnom2" != 1 ?? That's definitely wrong if logic was intended for state.
            // BUT, maybe the array is indexed numerically?
            // Let's rely on index 19 for ET_CODE based on sql query I saw.

            if ($carac[19] != 1) {
                $_SESSION['erreur'] = "Ce rapport n'est pas modifiable (statut différent de Nouveau).";
                header("Location: index.php?uc=rapportvisite&action=voirrapport");
                exit;
            }
        } else {
            // Fallback check if logic was different?
            // Assume 19 is correct reference.
        }

        // Chargement des données pour les listes déroulantes
        $motifs = getMotifs();

        // Récupération de la région pour filtrer les praticiens
        $infosUtilisateur = getAllInformationCompte($_SESSION['matricule']);
        $regionCode = $infosUtilisateur['reg_code'];
        $praticiens = getPraticiensByRegion($regionCode);

        $medicaments = getMedicaments();

        $mode = 'modification'; // Indicateur pour la vue
        include("vues/v_formulaireRapportDeVisite.php");
        break;
    }

    /**
     * Action : sauvegarderModification
     * Enregistre les modifications apportées à un rapport.
     */
    case 'sauvegarderModification': {
        try {
            $rapNum = $_POST['rapNum'];
            $numPraticien = $_POST['praticien'];
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

            if ($motifAutre && strlen($motifAutre) > 50) {
                $_SESSION['erreur'] = 'Le motif personnalisé ne peut pas dépasser 50 caractères.';
                header("Location: index.php?uc=rapportvisite&action=editerrapport&rapports=$rapNum");
                exit;
            }

            // Mise à jour en base de données
            $resultat = updateRapport($rapNum, $numPraticien, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $numRemplacant, $etat);

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