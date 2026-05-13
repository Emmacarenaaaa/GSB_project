<?php
include 'modele/bd.inc.php';
try {
    $pdo = connexionPDO();
    $stm = $pdo->query("DESCRIBE collaborateur");
    $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
