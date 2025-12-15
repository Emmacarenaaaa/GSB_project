<?php
require_once 'modele/praticien.modele.inc.php';
require_once 'modele/bd.inc.php';

if (!isset($_REQUEST['action']) || empty($_REQUEST['action'])) {
	$action = "formulaireprat";
} else {
	$action = $_REQUEST['action'];
}
switch ($action) {
	case 'formulaireprat': {
		// ce qu'on affiche dans "praticiens" 
		if (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] == 3 && isset($_SESSION['sec_code'])) {
			$result = getPraticiensBySecteur($_SESSION['sec_code']);
		} elseif (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] == 2 && isset($_SESSION['reg_code'])) {
			$result = getAllNomPraticien();
		} else {
			$result = getAllNomPraticien();
		}
		include("vues/v_formulairePraticien.php");
		break;
	}
	// ce qui est affiché lors de la séléction du praticien (consulter)
	case 'afficherprat': {
		$monPdo = connexionPDO();
		$prat = isset($_REQUEST['praticien']) ? $_REQUEST['praticien'] : (isset($_REQUEST['praticien_select']) ? $_REQUEST['praticien_select'] : null);
		if ($prat && getAllInformationPraticienNum($prat)) {
			$carac = getAllInformationPraticienNum($prat);
			if (empty($carac[9])) {
				$carac[9] = 'Non défini(e)';
			}

			// Récupérer les spécialités pour l'affichage
			$reqSpe = 'SELECT s.SPE_LIBELLE FROM specialite s 
                       INNER JOIN posseder p ON p.SPE_CODE = s.SPE_CODE 
                       WHERE p.PRA_NUM = ' . $prat;
			$resSpe = $monPdo->query($reqSpe);
			$specialites_affichage = $resSpe->fetchAll(PDO::FETCH_COLUMN);

			include("vues/v_afficherPraticien.php");
		} else {
			$_SESSION['erreur'] = true;
			header("Location: index.php?uc=praticien&action=formulaireprat");
		}
		break;
	}

	case 'modif': {
		// Vérifier les droits : Visiteur (1) n'a pas accès
		if (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] == 1) {
			$_SESSION['erreur'] = "Vous n'avez pas les droits pour gérer les praticiens.";
			header("Location: index.php?uc=praticien&action=formulaireprat");
			exit;
		}

		include_once 'modele/praticien.modele.inc.php';
		include_once 'modele/bd.inc.php';

		$monPdo = connexionPDO();
		$message = '';
		$errors = [];
		$mode = 'liste';
		$infosPrat = null;
		$specialites_prat = [];

		// Récupérer tous les praticiens avec filtre
		if (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] == 3 && isset($_SESSION['sec_code'])) {
			$praticiens = getPraticiensBySecteur($_SESSION['sec_code']);
		} elseif (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] == 2 && isset($_SESSION['reg_code'])) {
			$praticiens = getPraticiensByRegion($_SESSION['reg_code']);
		} else {
			$praticiens = getAllNomPraticien();
		}

		// Récupérer les types et spécialités
		$reqTypes = 'SELECT TYP_CODE, TYP_LIBELLE FROM type_praticien ORDER BY TYP_LIBELLE';
		$resTypes = $monPdo->query($reqTypes);
		$types = $resTypes->fetchAll();
		$reqSpecialites = 'SELECT SPE_CODE, SPE_LIBELLE FROM specialite ORDER BY SPE_LIBELLE';
		$resSpecialites = $monPdo->query($reqSpecialites);
		$specialites = $resSpecialites->fetchAll();
		//permet de ne pas remplacer l'action au niveau du controleur (clé post pour les actions internes)
		$formAction = isset($_POST['form_action']) ? $_POST['form_action'] : null;

		//  affichage de la liste de sélection 
		if ($formAction === null || $formAction == 'liste') {
			$mode = 'liste';
		}

		// MODE EDITER : sélection d'un praticien existant
		if (isset($_POST['praticien_select']) && !empty($_POST['praticien_select']) && ($formAction === null || $formAction != 'valider')) {
			$mode = 'editer';
			$numPrat = $_POST['praticien_select'];
			$infosPrat = getAllInformationPraticienNum($numPrat);

			$reqSpe = 'SELECT SPE_CODE FROM posseder WHERE PRA_NUM = ?';
			$resSpe = $monPdo->prepare($reqSpe);
			$resSpe->execute(array($numPrat));
			$specialites_prat = $resSpe->fetchAll(PDO::FETCH_COLUMN);
		}

		// MODE EDITER : création d'un nouveau praticien
		if ($formAction == 'creer' || (isset($_GET['option']) && $_GET['option'] == 'creer')) {
			$mode = 'editer';
			$infosPrat = array(
				'matriculepraticien' => '',
				'nom' => '',
				'prenom' => '',
				'adresse' => '',
				'codepostal' => '',
				'ville' => '',
				'coefficientdenotoriete' => '',
				'coefficientdeconfiance' => '',
				'typedepraticien' => ''
			);
			$specialites_prat = [];
		}

		// MODE VALIDER : traiter la soumission du formulaire
		if ($formAction == 'valider') {
			$num = isset($_POST['pranum']) ? $_POST['pranum'] : '';
			$nom = isset($_POST['pranom']) ? $_POST['pranom'] : '';
			$prenom = isset($_POST['praprenom']) ? $_POST['praprenom'] : '';
			$adresse = isset($_POST['praadresse']) ? $_POST['praadresse'] : '';
			$cp = isset($_POST['pracp']) ? $_POST['pracp'] : '';
			$ville = isset($_POST['praville']) ? $_POST['praville'] : '';
			$notoriete = isset($_POST['pracoefnotoriete']) ? $_POST['pracoefnotoriete'] : '';
			$confiance = isset($_POST['pracoefconfiance']) ? $_POST['pracoefconfiance'] : '';
			$type = isset($_POST['typcode']) ? $_POST['typcode'] : '';
			$specialites_sel = isset($_POST['specialites']) ? $_POST['specialites'] : [];

			// Validation des champs obligatoires
			if (empty($nom)) {
				$errors[] = "Le nom du praticien est obligatoire";
			}
			if (empty($prenom)) {
				$errors[] = "Le prénom du praticien est obligatoire";
			}
			if (empty($type)) {
				$errors[] = "Le type de praticien est obligatoire";
			}



			// Si pas d'erreurs, enregistrer en base
			if (empty($errors)) {
				try {
					if (empty($num)) {
						// Créer un nouveau praticien
						// Générer un nouveau numéro de praticien
						$reqMax = 'SELECT MAX(PRA_NUM) as max_num FROM praticien';
						$resMax = $monPdo->query($reqMax);
						$rowMax = $resMax->fetch();
						$num = $rowMax['max_num'] + 1;

						$reqInsert = 'INSERT INTO praticien (PRA_NUM, PRA_NOM, PRA_PRENOM, PRA_ADRESSE, PRA_CP, PRA_VILLE, PRA_COEFNOTORIETE, PRA_COEFCONFIANCE, TYP_CODE) 
             VALUES (:num, :nom, :prenom, :adresse, :cp, :ville, :notoriete, :confiance, :type)';
						$resInsert = $monPdo->prepare($reqInsert);
						$resInsert->bindParam(':num', $num, PDO::PARAM_INT);
						$resInsert->bindParam(':nom', $nom, PDO::PARAM_STR);
						$resInsert->bindParam(':prenom', $prenom, PDO::PARAM_STR);
						$resInsert->bindParam(':adresse', $adresse, PDO::PARAM_STR);
						$resInsert->bindParam(':cp', $cp, PDO::PARAM_STR);
						$resInsert->bindParam(':ville', $ville, PDO::PARAM_STR);
						$resInsert->bindParam(':notoriete', $notoriete, PDO::PARAM_INT);
						$resInsert->bindParam(':confiance', $confiance, PDO::PARAM_INT);
						$resInsert->bindParam(':type', $type, PDO::PARAM_STR);
						$resInsert->execute();

						$message = "Praticien créé avec succès !";
					} else {
						// Modifier un praticien existant
						$reqUpdate = 'UPDATE praticien SET PRA_NOM=?, PRA_PRENOM=?, PRA_ADRESSE=?, PRA_CP=?, PRA_VILLE=?, PRA_COEFNOTORIETE=?, PRA_COEFCONFIANCE=?, TYP_CODE=? 
										 WHERE PRA_NUM=?';
						$resUpdate = $monPdo->prepare($reqUpdate);
						$result = $resUpdate->execute(array($nom, $prenom, $adresse, $cp, $ville, $notoriete, $confiance, $type, $num));
						if ($result) {
							$message = "Praticien modifié avec succès !";
						} else {
							$errors[] = "Erreur lors de la mise à jour";
						}
					}

					// Gérer les spécialités
					if (empty($errors)) {
						$reqDeleteSpe = 'DELETE FROM posseder WHERE PRA_NUM = ?';
						$resDeleteSpe = $monPdo->prepare($reqDeleteSpe);
						$resDeleteSpe->execute(array($num));

						foreach ($specialites_sel as $spe) {
							$reqInsertSpe = 'INSERT INTO posseder (PRA_NUM, SPE_CODE, POS_DIPLOME, POS_COEFPRESCRIPTIO) VALUES (?, ?, ?, ?)';
							$resInsertSpe = $monPdo->prepare($reqInsertSpe);
							$resInsertSpe->execute(array($num, $spe, '', 0));
						}

						$mode = 'succes';
					} else {
						$mode = 'editer';
						$infosPrat = array(
							'matriculepraticien' => $num,
							'nom' => $nom,
							'prenom' => $prenom,
							'adresse' => $adresse,
							'codepostal' => $cp,
							'ville' => $ville,
							'coefficientdenotoriete' => $notoriete,
							'coefficientdeconfiance' => $confiance,
							'typedepraticien' => $type
						);
						$specialites_prat = $specialites_sel;
					}
				} catch (PDOException $e) {
					$errors[] = "Erreur base de données : " . $e->getMessage();
					$mode = 'editer';
					$infosPrat = array(
						'matriculepraticien' => $num,
						'nom' => $nom,
						'prenom' => $prenom,
						'adresse' => $adresse,
						'codepostal' => $cp,
						'ville' => $ville,
						'coefficientdenotoriete' => $notoriete,
						'coefficientdeconfiance' => $confiance,
						'typedepraticien' => $type
					);
					$specialites_prat = $specialites_sel;
				}
			} else {
				// Erreur de validation, rester en édition
				$mode = 'editer';
				$infosPrat = array(
					'matriculepraticien' => $num,
					'nom' => $nom,
					'prenom' => $prenom,
					'adresse' => $adresse,
					'codepostal' => $cp,
					'ville' => $ville,
					'coefficientdenotoriete' => $notoriete,
					'coefficientdeconfiance' => $confiance,
					'typedepraticien' => $type
				);
				$specialites_prat = $specialites_sel;
			}
		}

		include("vues/v_modifPraticien.php");
		break;
	}

	default: {
		header('Location: index.php?uc=praticien&action=formulaireprat');
		break;
	}
}
?>