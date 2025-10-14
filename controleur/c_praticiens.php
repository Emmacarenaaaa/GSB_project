<?php
if (!isset($_REQUEST['action']) || empty($_REQUEST['action'])) {
	$action = "formulaireprat";
} else {
	$action = $_REQUEST['action'];
}
switch ($action) {
	case 'formulaireprat': {

			$result = getAllNomPraticien();
			include("vues/v_formulairePraticien.php");
			break;
		}

	case 'afficherprat': {

			if (isset($_REQUEST['praticien']) && getAllInformationPraticienNum($_REQUEST['praticien'])) {
				$prat = $_REQUEST['praticien'];
				$carac = getAllInformationPraticienNum($prat);
				if (empty($carac[9])) {
					$carac[9] = 'Non défini(e)';
				}
				include("vues/v_afficherPraticien.php");
			} else {
				$_SESSION['erreur'] = true;
				header("Location: index.php?uc=praticien&action=formulaireprat");
			}
			break;
		}

	default: {

			header('Location: index.php?uc=praticien&action=formulaireprat');
			break;
		}
}
?>