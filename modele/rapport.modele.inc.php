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

function insertRapport($matricule, $numPraticien, $dateVisite, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $numRemplacant, $etat, $echantillonsOfferts)
{
    $monPdo = null;
    try {
        $monPdo = connexionPDO();
        $monPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $monPdo->beginTransaction(); // 1. DÉMARRER LA TRANSACTION

        $getMatricule = $monPdo->prepare("SELECT COL_MATRICULE FROM collaborateur WHERE COL_MATRICULE = ?");
        $getMatricule->execute([$matricule]);
        $matriculeExact = $getMatricule->fetchColumn();

        if (!$matriculeExact) {
            throw new Exception("Matricule inexistant");
        }

        $reqNum = "SELECT IFNULL(MAX(RAP_NUM), 0) + 1 AS prochain_num 
                   FROM rapport_visite 
                   WHERE COL_MATRICULE = ?";
        $stmtNum = $monPdo->prepare($reqNum);
        $stmtNum->execute([$matriculeExact]);
        $rapNum = $stmtNum->fetchColumn();
        if (!$rapNum)
            $rapNum = 1;


        // 2. INSERTION DU RAPPORT DANS RAPPORT_VISITE
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

        // BIND (Gestion des NULLs omise ici pour la concision, mais doit être complète dans votre code)
        $stmt->bindValue(':col_matricule', $matriculeExact, PDO::PARAM_STR);
        $stmt->bindValue(':rap_num', $rapNum, PDO::PARAM_INT);
        $stmt->bindValue(':pra_num', $numPraticien, PDO::PARAM_INT);
        $stmt->bindValue(':rap_datevisite', $dateVisite, PDO::PARAM_STR);
        $stmt->bindValue(':mo_code', $motif, PDO::PARAM_INT);
        if ($motifAutre === null) {
            $stmt->bindValue(':rap_motif_autre', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':rap_motif_autre', $motifAutre, PDO::PARAM_STR);
        }
        $stmt->bindValue(':rap_bilan', $bilan, PDO::PARAM_STR);
        if ($medoc1 === null) {
            $stmt->bindValue(':medoc1', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':medoc1', $medoc1, PDO::PARAM_STR);
        }
        if ($medoc2 === null) {
            $stmt->bindValue(':medoc2', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':medoc2', $medoc2, PDO::PARAM_STR);
        }
        if ($numRemplacant === null) {
            $stmt->bindValue(':numRemplacant', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':numRemplacant', $numRemplacant, PDO::PARAM_INT);
        }
        $stmt->bindValue(':et_code', $etat, PDO::PARAM_INT);

        $stmt->execute();

        // 3. INSERTION DES ÉCHANTILLONS DANS OFFRIR (CLE ÉTRANGÈRE RESPECTÉE)
        insertEchantillonsOfferts($monPdo, $matriculeExact, $rapNum, $echantillonsOfferts);

        $monPdo->commit(); // 4. VALIDATION FINALE

        error_log("Rapport inséré avec succès !");
        return true;

    } catch (PDOException $e) {
        if ($monPdo && $monPdo->inTransaction()) {
            $monPdo->rollBack(); // ANNULER la transaction
        }
        error_log("Erreur SQL (Transaction annulée) : " . $e->getMessage());
        return false;

    } catch (Exception $e) {
        error_log("Erreur : " . $e->getMessage());
        return false;
    }
}
function updateRapport($rapNum, $numPraticien, $motif, $motifAutre, $bilan, $medoc1, $medoc2, $numRemplacant, $etat, $echantillonsOfferts, $dateVisite)
{
    $monPdo = null;
    try {
        $monPdo = connexionPDO();
        $monPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $monPdo->beginTransaction();

        // 1. RÉCUPÉRER LE MATRICULE (OBLIGATOIRE POUR LA CLÉ COMPOSITE ET LA TABLE OFFRIR)
        $reqMatricule = "SELECT COL_MATRICULE FROM rapport_visite WHERE RAP_NUM = :rap_num";
        $stmtMatricule = $monPdo->prepare($reqMatricule);
        $stmtMatricule->bindValue(':rap_num', $rapNum, PDO::PARAM_INT);
        $stmtMatricule->execute();
        $matriculeExact = $stmtMatricule->fetchColumn();

        if (!$matriculeExact) {
            throw new Exception("Matricule du rapport non trouvé. Mise à jour impossible.");
        }


        // 2. UPDATE rapport_visite (Utilisation de la clé composite)
        $req = "UPDATE rapport_visite SET 
                PRA_NUM = :pra_num, MO_CODE = :mo_code, 
                RAP_MOTIF_AUTRE = :rap_motif_autre,
                RAP_BILAN = :rap_bilan, 
                MED_DEPOTLEGAL_PRESENTER1 = :medoc1, 
                MED_DEPOTLEGAL_PRESENTER2 = :medoc2, 
                PRA_NUM_REMPLACANT = :numRemplacant,
                RAP_DATEVISITE = :rap_datevisite,
                ET_CODE = :et_code
                WHERE RAP_NUM = :rap_num AND COL_MATRICULE = :col_matricule"; // CLÉ COMPOSITE

        $stmt = $monPdo->prepare($req);

        // BINDING
        $stmt->bindValue(':col_matricule', $matriculeExact, PDO::PARAM_STR);
        $stmt->bindValue(':rap_num', $rapNum, PDO::PARAM_INT);
        $stmt->bindValue(':pra_num', $numPraticien, PDO::PARAM_INT);
        $stmt->bindValue(':rap_datevisite', $dateVisite, PDO::PARAM_STR);
        $stmt->bindValue(':mo_code', $motif, PDO::PARAM_INT);
        if ($motifAutre === null) {
            $stmt->bindValue(':rap_motif_autre', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':rap_motif_autre', $motifAutre, PDO::PARAM_STR);
        }
        $stmt->bindValue(':rap_bilan', $bilan, PDO::PARAM_STR);
        if ($medoc1 === null) {
            $stmt->bindValue(':medoc1', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':medoc1', $medoc1, PDO::PARAM_STR);
        }
        if ($medoc2 === null) {
            $stmt->bindValue(':medoc2', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':medoc2', $medoc2, PDO::PARAM_STR);
        }
        if ($numRemplacant === null) {
            $stmt->bindValue(':numRemplacant', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':numRemplacant', $numRemplacant, PDO::PARAM_INT);
        }
        $stmt->bindValue(':et_code', $etat, PDO::PARAM_INT);
        $stmt->execute();

        // 3. MISE À JOUR DES ÉCHANTILLONS (OFFRIR)
        updateEchantillonsOfferts($monPdo, $matriculeExact, $rapNum, $echantillonsOfferts);

        $monPdo->commit();

        return true;

    } catch (PDOException $e) {
        if ($monPdo && $monPdo->inTransaction()) {
            $monPdo->rollBack();
        }

        error_log("Erreur updateRapport : " . $e->getMessage());
        return false;
    }
}
/**
 * Insère les échantillons offerts pour un rapport donné (doit être appelée dans une transaction).
 * @param PDO $monPdo La connexion PDO ouverte.
 * @param string $matricule Le matricule du collaborateur.
 * @param int $rapNum Le numéro du rapport.
 * @param array $echantillonsOfferts Tableau des échantillons à insérer.
 * @return bool Vrai en cas de succès.
 */
function insertEchantillonsOfferts(PDO $monPdo, $matricule, $rapNum, $echantillonsOfferts)
{
    if (empty($echantillonsOfferts)) {
        return true; // Rien à insérer
    }

    $reqInsert = "INSERT INTO offrir (COL_MATRICULE, RAP_NUM, MED_DEPOTLEGAL, qte_off) 
             VALUES (:col_matricule, :rap_num, :med_depotlegal, :qte_off)";
    $stmtInsert = $monPdo->prepare($reqInsert);

    foreach ($echantillonsOfferts as $offre) {


        $stmtInsert->bindValue(':col_matricule', $matricule, PDO::PARAM_STR);
        $stmtInsert->bindValue(':rap_num', $rapNum, PDO::PARAM_INT);
        $stmtInsert->bindValue(':med_depotlegal', $offre['medoc_id'], PDO::PARAM_STR);
        $stmtInsert->bindValue(':qte_off', $offre['quantite'], PDO::PARAM_INT);

        $stmtInsert->execute();
    }
    return true;
}
/**
 * Met à jour les échantillons offerts pour un rapport existant (DELETE + INSERT).
 * Doit être appelée dans une transaction.
 * @param PDO $monPdo La connexion PDO ouverte.
 * @param string $matricule Le matricule du collaborateur.
 * @param int $rapNum Le numéro du rapport.
 * @param array $echantillonsOfferts Tableau des échantillons à insérer.
 * @return bool Vrai en cas de succès.
 */
function updateEchantillonsOfferts(PDO $monPdo, $matricule, $rapNum, $echantillonsOfferts)
{
    $reqDelete = "DELETE FROM offrir WHERE COL_MATRICULE = :col_matricule AND RAP_NUM = :rap_num";
    $stmtDelete = $monPdo->prepare($reqDelete);
    $stmtDelete->bindValue(':col_matricule', $matricule, PDO::PARAM_STR);
    $stmtDelete->bindValue(':rap_num', $rapNum, PDO::PARAM_INT);
    $stmtDelete->execute();

    return insertEchantillonsOfferts($monPdo, $matricule, $rapNum, $echantillonsOfferts);
}
?>