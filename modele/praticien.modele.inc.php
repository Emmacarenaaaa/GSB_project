<?php

include_once 'bd.inc.php';

function getAllNomPraticien(){

    try{
        $monPdo = connexionPDO();
        $req = 'SELECT PRA_NUM, PRA_PRENOM, PRA_NOM FROM praticien ORDER BY PRA_NOM';
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

function getAllInformationPraticienNum($matricule){

    try{
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
        WHERE p.PRA_NUM = "'.$matricule.'"';
        $res = $monPdo->query($req);
        $result = $res->fetch();    
        return $result;
    } 
    
    catch (PDOException $e){
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getAllInformationPraticienNom($nom){

    try{
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
        FROM praticien p INNER JOIN type_praticien t ON t.TYP_CODE = p.TYP_CODE WHERE p.PRA_NOM = "'.$nom.'"';
        $res = $monPdo->query($req);
        $result = $res->fetch();    
        return $result;
    } 
    
    catch (PDOException $e){
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getPraticien($nom){

    try{
        $monPdo = connexionPDO();
        $req = 'SELECT PRA_NUM, PRA_PRENOM, PRA_NOM FROM praticien WHERE PRA_NOM = "'.$nom.'"';
        $res = $monPdo->query($req);
        $result = $res->fetch();    
        return $result;
    } 
    
    catch (PDOException $e){
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

function getNbPraticien(){

    try{
        $monPdo = connexionPDO();
        $req = 'SELECT COUNT(PRA_NUM) as \'nb\' FROM praticien';
        $res = $monPdo->query($req);
        $result = $res->fetch();    
        return $result;
    } 
    
    catch (PDOException $e){
        print "Erreur !: " . $e->getMessage();
        die();
    }
}

?>