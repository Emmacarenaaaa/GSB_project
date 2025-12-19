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
        $habId = $_SESSION['hab_id'];

        // Si responsable secteur (hab_id = 3) ou Délégué (hab_id = 2)
        if ($habId == 2 || $habId == 3) {
            // Pour les cadres, on filtre par Visiteur (Collaborateur)
            if ($habId == 3) {
                // Responsable : Visiteurs du secteur
                $secteur = $_SESSION['sec_code'];
                $lesVisiteurs = getCollaborateursBySecteur($secteur);
            } else {
                // Délégué : Visiteurs de la région
                $regionCode = $infosUtilisateur['reg_code'];
                $lesVisiteurs = getCollaborateursByRegion($regionCode);
            }
        } else {
            // Visiteur (hab_id = 1) : On filtre par Praticien
            $regionCode = $infosUtilisateur['reg_code'];
            $praticiens = getPraticiensByRegion($regionCode); // Utiliser la méthode du modèle
        }

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

        // Validation : Date de début et Date de fin sont obligatoires
        if (empty($dateDebut) || empty($dateFin)) {
            $_SESSION['erreur'] = true;
            // Redirection vers la selection avec le flag erreur
            header("Location: index.php?uc=rapportvisite&action=voirrapport");
            exit();
        }

        // Le filtre visiteur est récupéré s'il existe
        $visiteurFiltre = isset($_POST['visiteurFiltre']) && !empty($_POST['visiteurFiltre']) ? $_POST['visiteurFiltre'] : null;

        // Récupérer les infos de l'utilisateur connecté pour obtenir sa région et son secteur
        $infosUtilisateur = getAllInformationCompte($_SESSION['matricule']);
        $regionCode = $infosUtilisateur['reg_code'];
        $habId = $_SESSION['hab_id']; // Niveau d'habilitation
        $secCode = isset($_SESSION['sec_code']) ? $_SESSION['sec_code'] : null;

        // Restriction pour les Visiteurs (hab_id = 1) : ils ne voient que leurs propres rapports
        if ($habId == 1) {
            $visiteurFiltre = $_SESSION['matricule'];
        }

        // Restriction pour les Responsables de Secteur (niveau 3) : filtrage par secteur
        $secteurFiltre = null;
        if ($habId == 3) {
            $secteurFiltre = $secCode;
        }

        // Note: Pour les Délégués (2) et Responsables (3), $visiteurFiltre contient la sélection du formulaire (ou null = tous)

        // Appel au modèle pour récupérer la liste des rapports filtrés
        $result = getAllRapportDeVisite($dateDebut, $dateFin, $praticienFiltre, $regionCode, $visiteurFiltre, $secteurFiltre);

        include("vues/v_listeRapports.php");
        break;
    }

    /**
     * Action : mesRapportsBrouillon
     * Affiche la liste des rapports à l'état "Brouillon" pour l'utilisateur connecté.
     */
    case 'nouveauxRapportsRegion': {
        $habilitations = getAllInformationCompte($_SESSION['matricule']);
        $region = $habilitations['reg_code'];
        $secteur = $habilitations['sec_code'];
        $habId = $_SESSION['hab_id'];

        if ($habId == 2) {
            // Délégué Régional : Voir sa région
            $result = getNouveauxRapports($region, null);
            include("vues/v_listeRapportsRegion.php");
        } elseif ($habId == 3) {
            // Responsable Secteur : Voir son secteur
            $result = getNouveauxRapports(null, $secteur);
            include("vues/v_listeRapportsRegion.php");
        } else {
            header("Location: index.php?uc=accueil");
        }
        break;
    }

    case 'mesRapportsBrouillon': {
        $matricule = $_SESSION['matricule'];
        // Récupère les rapports non validés/terminés
        $lesRapportsBrouillon = getLesRapportsBrouillon($matricule);
        include("vues/v_rapportsBrouillon.php");
        break;
    }

    case 'mesRapportsClos': {
        $matricule = $_SESSION['matricule'];
        // Récupère les rapports clos (validés et consultés)
        $result = getMesRapportsClos($matricule);
        include("vues/v_listeMesRapportsClos.php");
        break;
    }

    /**
     * Action : afficherrapport
     * Affiche le détail complet d'un rapport sélectionné.
     */
    case 'afficherrapport': {
        if (isset($_REQUEST['rapports'])) {
            $rapNum = $_REQUEST['rapports'];

            // On tente de récupérer le matricule dans l'URL (contexte historique/liste)
            // Sinon on prend celui de la session (contexte "mes rapports")
            $matriculeVisite = isset($_REQUEST['matricule']) ? $_REQUEST['matricule'] : $_SESSION['matricule'];

            // Récupère toutes les infos du rapport par son numéro ET le matricule
            $carac = getInfosRapport($rapNum, $matriculeVisite);

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

            // NOUVEAU : Si Délégué (2) ou Responsable (3) consulte un rapport Validé (1), on le passe en Consulté (2)
            // carac[19] est ET_CODE (voir getAllInformationRapportDeVisiteNum)
            if (isset($_SESSION['hab_id']) && ($_SESSION['hab_id'] == 2 || $_SESSION['hab_id'] == 3) && $carac[19] == 1) {
                setRapportConsulte($rapNum);
                // On met à jour l'affichage localement pour que l'utilisateur voie "Consulté" (optionnel, mais mieux)
                $carac[19] = 2;
                $carac[18] = 'Consulté'; // On suppose que le libellé 2 est 'Consulté'
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
            header('Location: index.php?uc=accueil');
            exit;
        }

        // Chargement des données nécessaires au formulaire (motifs, médicaments...)
        $motifs = getMotifs();

        // NOUVEAU : Vérifier s'il y a des brouillons
        $matricule = $_SESSION['matricule'];
        $lesRapportsBrouillon = getLesRapportsBrouillon($matricule);
        $forceCreate = isset($_GET['force']) && $_GET['force'] == 1;

        if (!empty($lesRapportsBrouillon) && !$forceCreate) {
            $autoDetectedDrafts = true; // Flag pour la vue
            include("vues/v_rapportsBrouillon.php");
            break; // On s'arrête là, on n'affiche pas le formulaire de création
        }

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
            // --- 1. Récupération et Assainissement des données POST ---

            // Données principales (conversion numérique)
            $matricule = $_SESSION['matricule'];
            $numPraticien = (int) ($_POST['praticien'] ?? 0);
            $motif = (int) ($_POST['motif'] ?? 0);
            $etat = (int) ($_POST['etat'] ?? 0); // État par défaut 0 (Nouveau) si non fourni

            // Données textuelles (assainissement HTML)
            $bilan_raw = $_POST['bilan'] ?? '';
            $bilan = trim(htmlspecialchars($bilan_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            $motifAutre_raw = $_POST['motif_autre'] ?? '';
            $motifAutre = ($motif == 4) ? trim(htmlspecialchars($motifAutre_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8')) : null;

            // Données optionnelles
            $dateVisite = $_POST['dateVisite'] ?? null; // Validation de la date plus bas
            $medoc1 = !empty($_POST['medoc1']) ? htmlspecialchars($_POST['medoc1'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
            $medoc2 = !empty($_POST['medoc2']) ? htmlspecialchars($_POST['medoc2'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
            $numRemplacant = !empty($_POST['numRemplacant']) ? (int) $_POST['numRemplacant'] : null;

            $matricule = $_SESSION['matricule'];

            // --- 2. RÉCUPÉRATION ET VALIDATION DES ÉCHANTILLONS ---

            $echantillonsOfferts = [];
            $maxEchantillons = 10;

            for ($i = 1; $i <= $maxEchantillons; $i++) {
                $medoc_key = "echantillon_medoc_{$i}";
                $qte_key = "echantillon_qte_{$i}";

                $medoc_id_raw = $_POST[$medoc_key] ?? null;
                $quantite = (int) ($_POST[$qte_key] ?? 0);

                if (empty($medoc_id_raw)) {
                    continue; // Skip empty rows
                }

                $medoc_id_safe = htmlspecialchars($medoc_id_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                // Check duplicates
                $isDuplicate = false;
                foreach ($echantillonsOfferts as $eo) {
                    if ($eo['medoc_id'] === $medoc_id_safe) {
                        $isDuplicate = true;
                        break;
                    }
                }
                if ($isDuplicate)
                    continue;

                $quantiteFinale = ($quantite > 0) ? $quantite : null;

                $echantillonsOfferts[] = [
                    'medoc_id' => $medoc_id_safe, // MED_DEPOTLEGAL
                    'quantite' => $quantiteFinale // Can be NULL
                ];
            }

            // --- 3. Validation des Données Principales ---

            // Validation de l'ID Praticien
            if ($numPraticien <= 0) {
                $_SESSION['erreur'] = 'Veuillez sélectionner un praticien valide.';
                header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                exit;
            }

            // Validation de la longueur (avec mb_strlen pour l'UTF-8)
            if (mb_strlen($bilan) > 255) {
                $_SESSION['erreur'] = 'Le bilan ne peut pas dépasser 255 caractères.';
                header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                exit;
            }

            // Validation spécifique pour le motif "Autre" (avec mb_strlen)
            if ($motif == 4) {
                if (empty($motifAutre)) {
                    $_SESSION['erreur'] = 'Veuillez préciser le motif.';
                    header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                    exit;
                }
                if (mb_strlen($motifAutre) > 50) {
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

            // --- 4. Insertion dans la base de données ---
            // La fonction insertRapport doit maintenant accepter un 11ème argument: $echantillonsOfferts

            $resultat = insertRapport(
                $matricule,
                $numPraticien,
                $dateVisite,
                $motif,
                $motifAutre,
                $bilan,
                $medoc1,
                $medoc2,
                $numRemplacant,
                $etat,
                $echantillonsOfferts // NOUVEAU ARGUMENT
            );

            if ($resultat) {
                // MISE À JOUR DU COEFFICIENT DE CONFIANCE (NOUVEAU)
                $coefConfiance = isset($_POST['coefConfiance']) ? $_POST['coefConfiance'] : null;
                // Si un remplaçant est défini, c'est lui qui est visité (donc son coef est maj)
                $targetPra = !empty($numRemplacant) ? $numRemplacant : $numPraticien;

                if ($coefConfiance !== null && $coefConfiance !== '') {
                    updateCoefConfiance($targetPra, $coefConfiance);
                }

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
        $matricule = $_SESSION['matricule'];
        // Récupération des données du rapport
        $carac = getInfosRapport($rapNum, $matricule);

        if ($carac === false) {
            $_SESSION['erreur'] = "Rapport introuvable.";
            header("Location: index.php?uc=rapportvisite&action=voirrapport");
            exit;
        }
        // Vérification de l'état (ET_CODE)
        // Vérifier si le rapport est bien en état "Nouveau" (1) pour autoriser la modif
        // Modification user : status 0 is draft. 
        if ($carac[19] != 0) {
            $_SESSION['erreur'] = "Ce rapport n'est pas modifiable.";
            header("Location: index.php?uc=rapportvisite&action=voirrapport");
            exit;
        }

        // Vérification des droits : Seul le créateur peut modifier son brouillon
        if ($carac[0] != $matricule) {
            $_SESSION['erreur'] = "Vous n'avez pas les droits pour modifier ce rapport.";
            header("Location: index.php?uc=rapportvisite&action=mesRapportsBrouillon");
            exit;
        }

        $echantillonsInitiaux = getEchantillonsOffertsByRapportNum($matricule, $rapNum);
        // Récupération des données du rapport  (retourne les données)
        $motifs = getMotifs();
        $medicaments = getMedicaments();
        // Récupération de la région pour filtrer les praticiens
        $infosUtilisateur = getAllInformationCompte($_SESSION['matricule']);
        $regionCode = $infosUtilisateur['reg_code'];
        $praticiens = getPraticiensByRegion($regionCode);

        // Ensure the report's practitioner is in the list
        $praNum = $carac[8];
        $found = false;
        foreach ($praticiens as $p) {
            if ($p['PRA_NUM'] == $praNum) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $specificPrat = getPraticienById($praNum);
            if ($specificPrat) {
                $praticiens[] = $specificPrat;
                // Re-sort by name
                $nom = array_column($praticiens, 'PRA_NOM');
                array_multisort($nom, SORT_ASC, $praticiens);
            }
        }

        // Le code du visiteur est déjà en session
        $mode = 'modification'; // Indicateur pour la vue

        include("vues/v_formulaireRapportDeVisite.php");
        break;
    }

    case 'sauvegarderModification': {
        try {
            $rapNum = (int) ($_POST['rapNum'] ?? 0);

            // VÉRIFICATION CRITIQUE : L'ID du rapport doit être valide
            if ($rapNum <= 0) {
                $_SESSION['erreur'] = 'Erreur critique : Identifiant du rapport manquant ou invalide.';
                header("Location: index.php?uc=rapportvisite&action=voirrapport");
                exit;
            }

            $matricule = $_SESSION['matricule'];

            $numPraticien = (int) ($_POST['praticien'] ?? 0);
            $motif = (int) ($_POST['motif'] ?? 0);
            $etat = (int) ($_POST['etat'] ?? 0);

            // Assainissement des chaînes
            $bilan_raw = $_POST['bilan'] ?? '';
            $bilan = trim(htmlspecialchars($bilan_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            $motifAutre_raw = $_POST['motif_autre'] ?? '';
            $motifAutre = ($motif == 4) ? trim(htmlspecialchars($motifAutre_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8')) : null;

            $medoc1 = !empty($_POST['medoc1']) ? htmlspecialchars($_POST['medoc1'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
            $medoc2 = !empty($_POST['medoc2']) ? htmlspecialchars($_POST['medoc2'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;

            $numRemplacant = !empty($_POST['numRemplacant']) ? (int) $_POST['numRemplacant'] : null;

            // --- 1. VÉRIFICATION DE LA LOGIQUE MÉTIER ---

            // Utilisation de mb_strlen pour les caractères UTF-8
            if (mb_strlen($bilan) > 255) {
                $_SESSION['erreur'] = 'Le bilan ne peut pas dépasser 255 caractères.';
                header("Location: index.php?uc=rapportvisite&action=editerrapport&rapports=$rapNum");
                exit;
            }

            if ($motif == 4 && empty($motifAutre)) {
                $_SESSION['erreur'] = 'Veuillez préciser le motif.';
                header("Location: index.php?uc=rapportvisite&action=editerrapport&rapports=$rapNum");
                exit;
            }

            if ($motifAutre && mb_strlen($motifAutre) > 50) {
                $_SESSION['erreur'] = 'Le motif personnalisé ne peut pas dépasser 50 caractères.';
                header("Location: index.php?uc=rapportvisite&action=editerrapport&rapports=$rapNum");
                exit;
            }

            // --- 2. RÉCUPÉRATION ET VALIDATION DES ÉCHANTILLONS ---

            $echantillonsOfferts = [];
            $maxEchantillons = 10;

            for ($i = 1; $i <= $maxEchantillons; $i++) {
                $medoc_key = "echantillon_medoc_{$i}";
                $qte_key = "echantillon_qte_{$i}";

                $medoc_id_raw = $_POST[$medoc_key] ?? null;
                $quantite = (int) ($_POST[$qte_key] ?? 0);

                if (empty($medoc_id_raw)) {
                    continue; // Skip empty rows, do not break
                }

                $medoc_id_safe = htmlspecialchars($medoc_id_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                // Check for duplicates
                $isDuplicate = false;
                foreach ($echantillonsOfferts as $eo) {
                    if ($eo['medoc_id'] === $medoc_id_safe) {
                        $isDuplicate = true;
                        break;
                    }
                }
                if ($isDuplicate)
                    continue;

                // Validation : Quantity can be null (user requirement)
                $quantiteFinale = ($quantite > 0) ? $quantite : null;

                $echantillonsOfferts[] = [
                    'medoc_id' => $medoc_id_safe, // MED_DEPOTLEGAL
                    'quantite' => $quantiteFinale // Can be NULL
                ];
            }

            $dateVisite = $_POST['dateVisite'] ?? null;

            // --- 3. MISE À JOUR EN BASE DE DONNÉES ---

            // Assurez-vous que votre fonction updateRapport a bien 10 arguments !
            $resultat = updateRapport(
                $matricule,
                $rapNum,
                $numPraticien,
                $motif,
                $motifAutre,
                $bilan,
                $medoc1,
                $medoc2,
                $numRemplacant,
                $etat,
                $echantillonsOfferts,
                $dateVisite
            );

            if ($resultat) {
                // MISE À JOUR DU COEFFICIENT DE CONFIANCE (NOUVEAU)
                $coefConfiance = isset($_POST['coefConfiance']) ? $_POST['coefConfiance'] : null;
                $targetPra = !empty($numRemplacant) ? $numRemplacant : $numPraticien;

                if ($coefConfiance !== null && $coefConfiance !== '') {
                    updateCoefConfiance($targetPra, $coefConfiance);
                }

                $_SESSION['succes'] = 'Rapport modifié avec succès !';
                header('Location: index.php?uc=rapportvisite&action=mesRapportsBrouillon');
                exit;
            } else {
                $_SESSION['erreur'] = 'Erreur lors de la modification (Échec de la BDD).';
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