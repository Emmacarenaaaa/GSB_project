<?php

include_once 'bd.inc.php';

function getAllNomPraticien()
{
    try {
        $monPdo = connexionPDO();
        $req = 'SELECT PRA_NUM, PRA_PRENOM, PRA_NOM FROM praticien ORDER BY PRA_NOM';
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
                JOIN departement d ON LEFT(p.PRA_CP, 2) = d.NoDEPT
                JOIN region r ON d.REG_CODE = r.REG_CODE
                WHERE r.SEC_CODE = "' . $secteur . '"
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
        $req = 'SELECT DISTINCT p.PRA_NUM, p.PRA_PRENOM, p.PRA_NOM 
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
?>