<?php

include_once 'bd.inc.php';

function getAllNomPraticien()
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT PRA_NUM,PRA_PRENOM, PRA_NOM FROM praticien ORDER BY PRA_NOM';
        $res = $monPdo->query($req);
        $result = $res->fetchAll();
        return $result;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getAllInformationPraticienNum($matricule)
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT p.PRA_NUM as \'matriculepraticien\',
        p.PRA_NOM as \'nom\',
        p.PRA_PRENOM as \'prenom\',
        p.PRA_ADRESSE as \'adresse\',
        p.PRA_CP as \'codepostal\' ,
        p.PRA_VILLE as \'ville\',
        p.PRA_COEFNOTORIETE as \'coefficientdenotoriete\',
        p.PRA_COEFCONFIANCE as \'coefficientdeconfiance\', 
        t.TYP_LIBELLE as \'typedepraticien\'
        FROM praticien p INNER JOIN type_praticien t ON t.TYP_CODE = p.TYP_CODE 
        WHERE p.PRA_NUM = "' . $matricule . '"';
        $res = $monPdo->query($req);
        $result = $res->fetch();
        return $result;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getAllInformationPraticienNom($nom)
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT p.PRA_NUM as \'matriculepraticien\',
        p.PRA_NOM as \'nom\',
        p.PRA_PRENOM as \'prenom\',
        p.PRA_ADRESSE as \'adresse\',
        p.PRA_CP as \'codepostal\' ,
        p.PRA_VILLE as \'ville\',
        p.PRA_COEFNOTORIETE as \'coefficientdenotoriete\',
        p.PRA_COEFCONFIANCE as \'coefficientdeconfiance\', 
        t.TYP_LIBELLE as \'typedepraticien\'
        FROM praticien p INNER JOIN type_praticien t ON t.TYP_CODE = p.TYP_CODE WHERE p.PRA_NOM = "' . $nom . '"';
        $res = $monPdo->query($req);
        $result = $res->fetch();
        return $result;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getPraticien($nom)
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT PRA_NUM, PRA_PRENOM, PRA_NOM FROM praticien WHERE PRA_NOM = "' . $nom . '"';
        $res = $monPdo->query($req);
        $result = $res->fetch();
        return $result;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getNbPraticien()
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT COUNT(PRA_NUM) as \'nb\' FROM praticien';
        $res = $monPdo->query($req);
        $result = $res->fetch();
        return $result;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getPraticiensBySecteur($secteur)
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT DISTINCT p.PRA_NUM, p.PRA_PRENOM, p.PRA_NOM 
                FROM praticien p
                WHERE SUBSTRING(p.PRA_CP, 1, 2) IN (
                    SELECT d.NoDEPT 
                    FROM departement d
                    INNER JOIN region r ON d.REG_CODE = r.REG_CODE
                    WHERE r.SEC_CODE = "' . $secteur . '"
                )
                ORDER BY p.PRA_NOM';
        $res = $monPdo->query($req);
        $result = $res->fetchAll();
        return $result;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getPraticiensByRegion($region)
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT DISTINCT p.PRA_NUM, p.PRA_PRENOM, p.PRA_NOM, p.PRA_COEFCONFIANCE 
                FROM praticien p
                JOIN departement d ON LEFT(p.PRA_CP, 2) = d.NoDEPT
                WHERE d.REG_CODE = "' . $region . '"
                ORDER BY p.PRA_NOM';
        $res = $monPdo->query($req);
        $result = $res->fetchAll();
        return $result;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getPraticiensVisites($region)
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT DISTINCT p.PRA_NUM, p.PRA_PRENOM, p.PRA_NOM 
                FROM praticien p
                JOIN departement d ON LEFT(p.PRA_CP, 2) = d.NoDEPT
                JOIN rapport_visite rv ON rv.PRA_NUM = p.PRA_NUM
                WHERE d.REG_CODE = "' . $region . '"
                ORDER BY p.PRA_NOM';
        $res = $monPdo->query($req);
        $result = $res->fetchAll();
        return $result;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getPraticiensVisitesBySecteur($secteur)
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT DISTINCT p.PRA_NUM, p.PRA_PRENOM, p.PRA_NOM 
                FROM praticien p
                JOIN rapport_visite rv ON rv.PRA_NUM = p.PRA_NUM
                WHERE SUBSTRING(p.PRA_CP, 1, 2) IN (
                    SELECT d.NoDEPT 
                    FROM departement d
                    INNER JOIN region r ON d.REG_CODE = r.REG_CODE
                    WHERE r.SEC_CODE = "' . $secteur . '"
                )
                ORDER BY p.PRA_NOM';
        $res = $monPdo->query($req);
        $result = $res->fetchAll();
        return $result;
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function updateCoefConfiance($praNum, $coef)
{
    try {
        $monPdo = connexionPDO();
        // Le coefficient peut être NULL (vide) ou decimal
        if ($coef === '' || $coef === null) {
            // Décision métier : garde-t-on l'ancien ou met-on NULL ? 
            // Si l'utilisateur efface le champ, on pourrait mettre NULL.
            // Supposons NULL permis ou 0.
            $req = "UPDATE praticien SET PRA_COEFCONFIANCE = NULL WHERE PRA_NUM = :praNum";
            $stmt = $monPdo->prepare($req);
        } else {
            $req = "UPDATE praticien SET PRA_COEFCONFIANCE = :coef WHERE PRA_NUM = :praNum";
            $stmt = $monPdo->prepare($req);
            $stmt->bindValue(':coef', $coef, PDO::PARAM_STR);
        }

        $stmt->bindValue(':praNum', $praNum, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        error_log("Erreur updateCoefConfiance : " . $e->getMessage());
        return false;
    }
}
?>