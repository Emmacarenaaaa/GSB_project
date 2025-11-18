<?php


include_once 'bd.inc.php';


function getAllrapportDeVisite($dateDebut = null, $dateFin = null, $praticienNum = null){
  try {
    $monPdo = connexionPDO();
    
    $req = 'SELECT r.RAP_NUM, r.RAP_DATEVISITE, c.COL_NOM, c.COL_PRENOM
            FROM rapport_visite r
            LEFT JOIN collaborateur c ON r.COL_MATRICULE = c.COL_MATRICULE
            WHERE 1=1';
    
    $params = [];
    
    if ($dateDebut !== null && $dateDebut !== '') {
      $req .= ' AND r.RAP_DATEVISITE >= :dateDebut';
      $params[':dateDebut'] = $dateDebut;
    }
    
    if ($dateFin !== null && $dateFin !== '') {
      $req .= ' AND r.RAP_DATEVISITE <= :dateFin';
      $params[':dateFin'] = $dateFin;
    }
    
    if ($praticienNum !== null && $praticienNum !== '') {
      $req .= ' AND r.PRA_NUM = :praticien';
      $params[':praticien'] = intval($praticienNum);
    }
    
    $req .= ' ORDER BY r.RAP_NUM';
    
    $res = $monPdo->prepare($req);
    $res->execute($params);
    $result = $res->fetchAll();
    
    return $result;
    
  } catch (PDOException $e) {
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

 // Récupérer la valeur exacte depuis collaborateur
        $getMatricule = $monPdo->prepare("SELECT COL_MATRICULE FROM collaborateur WHERE COL_MATRICULE = ?");
        $getMatricule->execute([$matricule]);
        $matriculeExact = $getMatricule->fetchColumn();
        
        if (!$matriculeExact) {
            throw new Exception("Matricule inexistant");
        }
        
        error_log("Matricule utilisé : '$matriculeExact'");

        // Calculer le prochain RAP_NUM
        $reqNum = "SELECT IFNULL(MAX(RAP_NUM), 0) + 1 AS prochain_num 
                   FROM rapport_visite 
                   WHERE COL_MATRICULE = ?";
        $stmtNum = $monPdo->prepare($reqNum);
        $stmtNum->execute([$matriculeExact]);
        $rapNum = $stmtNum->fetchColumn();
        if (!$rapNum) $rapNum = 1;
        
        error_log("RAP_NUM calculé : $rapNum");

        // INSERT
        $req = "INSERT INTO rapport_visite (
            COL_MATRICULE, RAP_NUM, PRA_NUM, RAP_DATEVISITE, MO_Code, RAP_MOTIF_AUTRE,
            RAP_BILAN, RAP_DATESAISIE,
            MED_DEPOTLEGAL_PRESENTER1, MED_DEPOTLEGAL_PRESENTER2, PRA_NUM_REMPLACANT, ET_CODE
        ) VALUES (
            :col_matricule, :rap_num, :pra_num, :rap_datevisite, :mo_code, :rap_motif_autre,
            :rap_bilan, NOW(),
            :medoc1, :medoc2, :numRemplacant, :et_code
        )";

        $stmt = $monPdo->prepare($req);

        // BIND tous les paramètres
        $stmt->bindParam(':col_matricule', $matriculeExact, PDO::PARAM_STR);
        $stmt->bindParam(':rap_num', $rapNum, PDO::PARAM_INT);
        $stmt->bindParam(':pra_num', $numPraticien, PDO::PARAM_INT);
        $stmt->bindParam(':rap_datevisite', $dateVisite, PDO::PARAM_STR);
        $stmt->bindParam(':mo_code', $motif, PDO::PARAM_INT);
        $stmt->bindParam(':rap_motif_autre', $motifAutre, PDO::PARAM_STR);
        $stmt->bindParam(':rap_bilan', $bilan, PDO::PARAM_STR);
        $stmt->bindParam(':medoc1', $medoc1, PDO::PARAM_STR);
        $stmt->bindParam(':medoc2', $medoc2, PDO::PARAM_STR);
        $stmt->bindParam(':numRemplacant', $numRemplacant, PDO::PARAM_INT);
        $stmt->bindParam(':et_code', $etat, PDO::PARAM_INT);

        // EXÉCUTER la requête INSERT
        $stmt->execute();
        
        error_log("Nombre de lignes insérées : " . $stmt->rowCount());
        error_log("✓✓✓ Rapport inséré avec succès !");
        
        return true;

    } catch (PDOException $e) {
        error_log("✗ Erreur SQL : " . $e->getMessage());
        return false;
        
    } catch (Exception $e) {
        error_log("✗ Erreur : " . $e->getMessage());
        return false;
    }
}

?>