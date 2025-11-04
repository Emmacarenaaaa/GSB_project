<?php


include_once 'bd.inc.php';


function getAllrapportDeVisite(){


  try{
    $monPdo = connexionPDO();
    $req = 'SELECT r.RAP_NUM, r.RAP_DATEVISITE, c.COL_NOM, c.COL_PRENOM
FROM rapport_visite r
LEFT JOIN collaborateur c ON r.COL_MATRICULE = c.COL_MATRICULE ORDER BY RAP_NUM';
    $res = $monPdo->query($req);
    $result = $res->fetchAll();
    return $result;
  } 


  catch (PDOException $e){
    print "Erreur !: " . $e->getMessage();
    die();
  }


}
  //,PRA_ADRESSE,PRA_CP, PRA_VILLE,PRA_COEFNOTORIETE,TYP_CODE


function getAllInformationRapportDeVisiteNum($rapNum){


  try{
    $monPdo = connexionPDO();
    $req = "
    SELECT
  r.COL_MATRICULE AS matriculepraticien,
  c.COL_NOM AS nomcollaborateur,
  c.COL_PRENOM AS prenomcollaborateur,

  r.RAP_NUM AS rapportnum,
  r.RAP_DATEVISITE AS datevisite,
  CASE
    WHEN r.RAP_MOTIF_AUTRE IS NOT NULL AND r.RAP_MOTIF_AUTRE <> ''
      THEN r.RAP_MOTIF_AUTRE
    ELSE m.MO_LIBELLE
  END AS motif,

  r.RAP_BILAN AS bilan,
  r.RAP_DATESAISIE AS datesaisie,

  p.PRA_NUM AS numpraticien,
  p.PRA_NOM AS nompraticien,
  p.PRA_PRENOM AS prenompraticien,

  pr.PRA_NUM AS numremplacant,
  pr.PRA_NOM AS nomremplacant,
  pr.PRA_PRENOM AS prenomremplacant,

  md1.MED_DEPOTLEGAL AS medocpresenter1,
  md2.MED_DEPOTLEGAL AS medocpresenter2,
  e.ETAT_LIBELLE AS etatrapport
  
FROM rapport_visite r
LEFT JOIN collaborateur c ON r.COL_MATRICULE = c.COL_MATRICULE
LEFT JOIN etat e ON r.ET_CODE = e.ETAT_CODE
LEFT JOIN praticien p ON r.PRA_NUM = p.PRA_NUM
LEFT JOIN motif m ON r.MO_CODE = m.MO_CODE
LEFT JOIN praticien pr ON r.PRA_NUM_REMPLACANT = pr.PRA_NUM
LEFT JOIN medicament md1 ON r.MED_DEPOTLEGAL_PRESENTER1 = md1.MED_DEPOTLEGAL
LEFT JOIN medicament md2 ON r.MED_DEPOTLEGAL_PRESENTER2 = md2.MED_DEPOTLEGAL
WHERE r.RAP_NUM = :rapNum;";


    $res = $monPdo->prepare($req);
    $res->bindParam(':rapNum', $rapNum, PDO::PARAM_INT);
    $res->execute(); 
    $result = array_values($res->fetch(PDO::FETCH_ASSOC));
    return $result;
  } 
  
  catch (PDOException $e){
    print "Erreur !: " . $e->getMessage();
    die();
  }
}

function getMotifs() {
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT MO_CODE, MO_LIBELLE FROM motif ORDER BY MO_LIBELLE';
        $result = $monPdo->query($req)->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        print "Erreur : " . $e->getMessage();
        die();
    }
}

function getAllPraticiens() {
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT PRA_NUM, PRA_NOM, PRA_PRENOM FROM praticien ORDER BY PRA_NOM';
        $result = $monPdo->query($req)->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        print "Erreur : " . $e->getMessage();
        die();
    }
}

function getMedicaments() {
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT MED_DEPOTLEGAL, MED_NOMCOMMERCIAL FROM medicament ORDER BY MED_NOMCOMMERCIAL';
        $result = $monPdo->query($req)->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        print "Erreur : " . $e->getMessage();
        die();
    }
}

function insertRapport($matricule, $numPraticien, $dateVisite, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $numRemplacant, $etat) {
    try {
        $monPdo = connexionPDO();
        $monPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $req = "INSERT INTO rapport_visite (
            COL_MATRICULE, PRA_NUM, RAP_DATEVISITE, MO_CODE, RAP_MOTIF_AUTRE,
            RAP_BILAN, RAP_DATESAISIE,
            MED_DEPOTLEGAL_PRESENTER1, MED_DEPOTLEGAL_PRESENTER2, PRA_NUM_REMPLACANT, ET_CODE
        ) VALUES (
            :col_matricule, :pra_num, :rap_datevisite, :mo_code, :rap_motif_autre,
            :rap_bilan, NOW(),
            :medoc1, :medoc2, :numRemplacant, :et_code
        )";

        $stmt = $monPdo->prepare($req);

        $stmt->bindParam(':col_matricule', $matricule, PDO::PARAM_INT);
        $stmt->bindParam(':pra_num', $numPraticien, PDO::PARAM_INT);
        $stmt->bindParam(':rap_datevisite', $dateVisite);
        $stmt->bindParam(':mo_code', $motif, PDO::PARAM_INT);

        if ($motifAutre === null) {
            $stmt->bindValue(':rap_motif_autre', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':rap_motif_autre', $motifAutre, PDO::PARAM_STR);
        }

        $stmt->bindParam(':rap_bilan', $bilan);

        if (empty($medoc1)) {
            $stmt->bindValue(':medoc1', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':medoc1', $medoc1);
        }

        if (empty($medoc2)) {
            $stmt->bindValue(':medoc2', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':medoc2', $medoc2);
        }

        if (empty($numRemplacant)) {
            $stmt->bindValue(':numRemplacant', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':numRemplacant', $numRemplacant, PDO::PARAM_INT);
        }

        $stmt->bindParam(':et_code', $etat, PDO::PARAM_INT);

        $stmt->execute();
        return true;

    } catch (PDOException $e) {
        // Pour debug, tu peux logger ou afficher :
        error_log("Erreur SQL : " . $e->getMessage());
        return false;
    }
}





?>