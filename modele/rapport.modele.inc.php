<?php

function getAllRapportDeVisite($dateDebut = null, $dateFin = null, $praticienFiltre = null, $region = null, $visiteur = null, $secteur = null)
{
    try {
        $monPdo = connexionPDO();
        $req = "SELECT r.RAP_NUM, r.RAP_DATEVISITE, c.COL_NOM, c.COL_PRENOM, 
                       p.PRA_NUM, p.PRA_NOM, p.PRA_PRENOM, 
                       m.MO_LIBELLE, r.RAP_MOTIF_AUTRE,
                       md1.MED_NOMCOMMERCIAL as MED1, md2.MED_NOMCOMMERCIAL as MED2
                FROM rapport_visite r
                LEFT JOIN collaborateur c ON r.COL_MATRICULE = c.COL_MATRICULE
                LEFT JOIN praticien p ON r.PRA_NUM = p.PRA_NUM
                LEFT JOIN motif m ON r.MO_CODE = m.MO_CODE
                LEFT JOIN medicament md1 ON r.MED_DEPOTLEGAL_PRESENTER1 = md1.MED_DEPOTLEGAL
                LEFT JOIN medicament md2 ON r.MED_DEPOTLEGAL_PRESENTER2 = md2.MED_DEPOTLEGAL";

        if ($secteur) {
            $req .= " LEFT JOIN region reg ON c.REG_CODE = reg.REG_CODE";
        }

        $req .= " WHERE 1=1 AND r.ET_CODE = 1"; // Afficher uniquement les rapports clos

        // Filtre par secteur
        if ($secteur) {
            // Prioritize Collaborator's direct sector if available (though schema seems to use Reg -> Sec usually for visibility?)
            // Based on schema, Collaborator has SEC_CODE directly too.
            // Let's filter by: (c.SEC_CODE = :secteur) OR (c.SEC_CODE IS NULL AND reg.SEC_CODE = :secteur)
            $req .= " AND (c.SEC_CODE = :secteur OR (c.SEC_CODE IS NULL AND reg.SEC_CODE = :secteur))";
        }

        // Filtre par région (obligatoire pour les utilisateurs connectés SI pas de secteur spécifié, ou cumulatif?)
        // Usually if we are filtering by Sector (Manager), we don't restrict to one Region unless requested.
        // Original code enforced Region filter if passed.
        if ($region && !$secteur) {
            $req .= " AND c.REG_CODE = :region";
        }

        // Filtre par visiteur
        if ($visiteur) {
            $req .= " AND r.COL_MATRICULE = :visiteur";
        }

        // Filtres existants
        if ($dateDebut) {
            $req .= " AND r.RAP_DATEVISITE >= :dateDebut";
        }
        if ($dateFin) {
            $req .= " AND r.RAP_DATEVISITE <= :dateFin";
        }
        if ($praticienFiltre) {
            $req .= " AND r.PRA_NUM = :praticien";
        }

        $req .= " ORDER BY r.RAP_DATEVISITE DESC, r.RAP_NUM DESC";

        $stmt = $monPdo->prepare($req);

        if ($secteur) {
            $stmt->bindValue(':secteur', $secteur, PDO::PARAM_STR);
        }

        // Only bind region if we are using it
        if ($region && !$secteur) {
            $stmt->bindValue(':region', $region, PDO::PARAM_STR);
        }
        if ($visiteur) {
            $stmt->bindValue(':visiteur', $visiteur, PDO::PARAM_STR);
        }
        if ($dateDebut) {
            $stmt->bindValue(':dateDebut', $dateDebut, PDO::PARAM_STR);
        }
        if ($dateFin) {
            $stmt->bindValue(':dateFin', $dateFin, PDO::PARAM_STR);
        }
        if ($praticienFiltre) {
            $stmt->bindValue(':praticien', $praticienFiltre, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getCollaborateursByRegion($region)
{
    try {
        $monPdo = connexionPDO();
        $req = "SELECT COL_MATRICULE, COL_NOM, COL_PRENOM 
                FROM collaborateur 
                WHERE REG_CODE = :region 
                ORDER BY COL_NOM, COL_PRENOM";

        $stmt = $monPdo->prepare($req);
        $stmt->bindValue(':region', $region, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}
function getCollaborateursBySecteur($secteur)
{
    try {
        $monPdo = connexionPDO();
        $req = "SELECT COL_MATRICULE, COL_NOM, COL_PRENOM 
                FROM collaborateur 
                WHERE SEC_CODE = :secteur 
                ORDER BY COL_NOM, COL_PRENOM";

        $stmt = $monPdo->prepare($req);
        $stmt->bindValue(':secteur', $secteur, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}
function getLesRapportsBrouillon($matricule)
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT r.RAP_NUM, r.RAP_DATEVISITE, c.COL_NOM, c.COL_PRENOM
                FROM rapport_visite r
                LEFT JOIN collaborateur c ON r.COL_MATRICULE = c.COL_MATRICULE
                WHERE r.COL_MATRICULE = :matricule AND r.ET_CODE = 0
                ORDER BY r.RAP_NUM';

        $res = $monPdo->prepare($req);
        $res->bindParam(':matricule', $matricule, PDO::PARAM_STR);
        $res->execute();
        $result = $res->fetchAll();

        return $result;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}


//,PRA_ADRESSE,PRA_CP, PRA_VILLE,PRA_COEFNOTORIETE,TYP_CODE


function getAllInformationRapportDeVisiteNum($rapNum)
{


    try {
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
  md1.MED_NOMCOMMERCIAL AS medocnom1,
  md2.MED_DEPOTLEGAL AS medocpresenter2,
  md2.MED_NOMCOMMERCIAL AS medocnom2,
  e.ETAT_LIBELLE AS etatrapport,
  r.ET_CODE AS etatcode
  
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
        $fetch = $res->fetch(PDO::FETCH_ASSOC);
        if ($fetch === false) {
            return false;
        }
        $result = array_values($fetch);
        return $result;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getMotifs()
{
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

function getAllPraticiens()
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT p.PRA_NUM, p.PRA_NOM, p.PRA_PRENOM, r.REG_NOM
                FROM praticien p
                LEFT JOIN departement d ON LEFT(p.PRA_CP, 2) = d.NoDEPT
                LEFT JOIN region r ON d.REG_CODE = r.REG_CODE
                ORDER BY r.REG_NOM, p.PRA_NOM';
        $result = $monPdo->query($req)->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        print "Erreur : " . $e->getMessage();
        die();
    }
}

function getMedicaments()
{
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

function insertRapport($matricule, $numPraticien, $dateVisite, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $numRemplacant, $etat)
{
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
        if (!$rapNum)
            $rapNum = 1;

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
        // BIND tous les paramètres
        $stmt->bindValue(':col_matricule', $matriculeExact, PDO::PARAM_STR);
        $stmt->bindValue(':rap_num', $rapNum, PDO::PARAM_INT);
        $stmt->bindValue(':pra_num', $numPraticien, PDO::PARAM_INT);
        $stmt->bindValue(':rap_datevisite', $dateVisite, PDO::PARAM_STR);
        $stmt->bindValue(':mo_code', $motif, PDO::PARAM_INT);
        $stmt->bindValue(':rap_motif_autre', $motifAutre, PDO::PARAM_STR);
        $stmt->bindValue(':rap_bilan', $bilan, PDO::PARAM_STR);
        $stmt->bindValue(':medoc1', $medoc1, PDO::PARAM_STR);
        $stmt->bindValue(':medoc2', $medoc2, PDO::PARAM_STR);
        $stmt->bindValue(':numRemplacant', $numRemplacant, PDO::PARAM_INT);
        $stmt->bindValue(':et_code', $etat, PDO::PARAM_INT);

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

function updateRapport($rapNum, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $numRemplacant, $etat)
{
    try {
        $monPdo = connexionPDO();
        $req = "UPDATE rapport_visite SET 
                MO_CODE = :mo_code, 
                RAP_MOTIF_AUTRE = :rap_motif_autre,
                RAP_BILAN = :rap_bilan, 
                MED_DEPOTLEGAL_PRESENTER1 = :medoc1, 
                MED_DEPOTLEGAL_PRESENTER2 = :medoc2, 
                PRA_NUM_REMPLACANT = :numRemplacant, 
                ET_CODE = :et_code
                WHERE RAP_NUM = :rap_num";

        $stmt = $monPdo->prepare($req);
        $stmt->bindValue(':rap_num', $rapNum, PDO::PARAM_INT);
        $stmt->bindValue(':mo_code', $motif, PDO::PARAM_INT);
        $stmt->bindValue(':rap_motif_autre', $motifAutre, PDO::PARAM_STR);
        $stmt->bindValue(':rap_bilan', $bilan, PDO::PARAM_STR);
        $stmt->bindValue(':medoc1', $medoc1, PDO::PARAM_STR);
        $stmt->bindValue(':medoc2', $medoc2, PDO::PARAM_STR);
        $stmt->bindValue(':numRemplacant', $numRemplacant, PDO::PARAM_INT);
        $stmt->bindValue(':et_code', $etat, PDO::PARAM_INT);

        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        error_log("Erreur updateRapport : " . $e->getMessage());
        return false;
    }
}

?>