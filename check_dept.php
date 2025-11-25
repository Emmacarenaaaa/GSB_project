<?php
include 'modele/bd.inc.php';
try {
    $pdo = connexionPDO();
    $stm = $pdo->query("SELECT NoDEPT, Departement FROM departement LIMIT 5");
    $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
    
    $stm2 = $pdo->query("SELECT PRA_CP FROM praticien LIMIT 5");
    $rows2 = $stm2->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows2);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
