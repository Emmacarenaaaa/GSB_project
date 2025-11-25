<?php
include 'modele/bd.inc.php';
try {
    $pdo = connexionPDO();
    $stm = $pdo->query("SHOW TABLES");
    $rows = $stm->fetchAll(PDO::FETCH_NUM);
    foreach ($rows as $row) {
        echo $row[0] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
