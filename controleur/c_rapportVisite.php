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
        
        // Si responsable secteur (hab_id = 3)
        if (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] == 3) {
            $secteur = $_SESSION['sec_code'];
            // Utiliser la nouvelle fonction basée sur la logique fournie
            $praticiens = getPraticiensVisitesBySecteur($secteur);
        } else {
            // Comportement par défaut (Visiteur / Délégué régional)
            $regionCode = $infosUtilisateur['reg_code'];
            $praticiens = getPraticiensVisites($regionCode);
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

        // Validation : Tous les champs sont obligatoires
        if (empty($dateDebut) || empty($dateFin) || empty($praticienFiltre)) {
            $_SESSION['erreur'] = true;
            // Redirection vers la selection avec le flag erreur
            header("Location: index.php?uc=rapportvisite&action=voirrapport");
            exit();
        }

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
            // --- 1. Récupération et Assainissement des données POST ---

            // Données principales (conversion numérique)
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
                    if ($i > 1 && count($echantillonsOfferts) > 0) {
                        break;
                    }
                    continue;
                }

                // Assainissement final
                $medoc_id_safe = htmlspecialchars($medoc_id_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                // Validation : La quantité doit être > 0 si le produit est sélectionné
                if ($quantite <= 0) {
                    $_SESSION['erreur'] = 'Veuillez spécifier une quantité positive pour l\'échantillon ' . $i . '.';
                    header('Location: index.php?uc=rapportvisite&action=saisirrapport');
                    exit;
                }

                $echantillonsOfferts[] = [
                    'medoc_id' => $medoc_id_safe,
                    'quantite' => $quantite
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
        $carac = getAllInformationRapportDeVisiteNum($rapNum);

        if ($carac === false) {
            $_SESSION['erreur'] = "Rapport introuvable.";
            header("Location: index.php?uc=rapportvisite&action=voirrapport");
            exit;
        }
        // Vérification de l'état (ET_CODE)
        // Vérifier si le rapport est bien en état "Nouveau" (1) pour autoriser la modif
        if ($carac[19] != 0) {
            $_SESSION['erreur'] = "Ce rapport n'est pas modifiable.";
            header("Location: index.php?uc=rapportvisite&action=voirrapport");
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

                // Récupérer les valeurs (null si non envoyées)
                $medoc_id_raw = $_POST[$medoc_key] ?? null;
                $quantite = (int) ($_POST[$qte_key] ?? 0);

                // Si le médicament est vide, on s'arrête (car les champs sont séquentiels)
                if (empty($medoc_id_raw)) {
                    // On s'arrête seulement si on a déjà traité au moins une ligne valide
                    if ($i > 1 && count($echantillonsOfferts) > 0) {
                        break;
                    }
                    // Si c'est la première ligne et qu'elle est vide, on continue pour voir si la 2ème est remplie (au cas où)
                    continue;
                }

                // Validation : La quantité doit être > 0 si le produit est sélectionné
                if ($quantite <= 0) {
                    $_SESSION['erreur'] = 'Veuillez spécifier une quantité positive pour l\'échantillon ' . $i . ' (' . $medoc_id_raw . ').';
                    header("Location: index.php?uc=rapportvisite&action=editerrapport&rapports=$rapNum");
                    exit;
                }

                // Assainissement final et stockage
                $medoc_id_safe = htmlspecialchars($medoc_id_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                $echantillonsOfferts[] = [
                    // La valeur 'medoc_id' est garantie non-vide ici,
                    // car les entrées vides sont ignorées ou causent une sortie anticipée.
                    'medoc_id' => $medoc_id_safe,
                    'quantite' => $quantite
                ];
            }

            $dateVisite = $_POST['dateVisite'] ?? null;

            // --- 3. MISE À JOUR EN BASE DE DONNÉES ---

            // Assurez-vous que votre fonction updateRapport a bien 10 arguments !
            $resultat = updateRapport(
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