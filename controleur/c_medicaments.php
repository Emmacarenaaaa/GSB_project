<?php
/**
 * Contrôleur gérant les actions liées aux médicaments
 */
if (!isset($_REQUEST['action']) || empty($_REQUEST['action'])) {
	$action = "formulairemedoc";
} else {
	$action = $_REQUEST['action'];
}

switch ($action) {
	// Affiche la liste déroulante ou le formulaire pour sélectionner un médicament
	case 'formulairemedoc': {
			$result = getAllNomMedicament();
			include("vues/v_formulaireMedicament.php");
			break;
		}

	// Affiche les informations détaillées du médicament sélectionné
	case 'affichermedoc': {
			if (isset($_REQUEST['medicament']) && getAllInformationMedicamentDepot($_REQUEST['medicament'])) {
				$med = $_REQUEST['medicament'];
				$carac = getAllInformationMedicamentDepot($med);
				// Si le prix d'échantillon n'est pas défini, on indique "Non défini(e)"
				if (empty($carac[7])) {
					$carac[7] = 'Non défini(e)';
				}
				include("vues/v_afficherMedicament.php");
			} else {
				$_SESSION['erreur'] = true;
				header("Location: index.php?uc=medicaments&action=formulairemedoc");
			}
			break;
		}

	default: {
			header('Location: index.php?uc=medicaments&action=formulairemedoc');
			break;
		}
}
?>