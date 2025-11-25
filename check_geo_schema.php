<?php
include 'modele/bd.inc.php';
try {
    $pdo = connexionPDO();
    echo "TABLE REGION:\n";
    $stm = $pdo->query("DESCRIBE region");
    $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "\nTABLE DEPARTEMENT:\n";
    $stm = $pdo->query("DESCRIBE departement");
    $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
