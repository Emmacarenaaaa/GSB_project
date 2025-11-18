<section class="bg-light">
    <div class="container">
        <div class="structure-hero pt-lg-5 pt-4">
            <h1 class="titre text-center">Gestion des praticiens</h1>
            <p class="text text-center">
                Interface de gestion permettant au délégué de créer, modifier 
                et consulter les praticiens de son secteur.
            </p>
        </div>

        <?php
        include_once 'modele/praticien.modele.inc.php';
        include_once 'modele/bd.inc.php';

        $monPdo = connexionPDO();
        $message = '';
        $errors = [];
        $mode = 'liste';
        $infosPrat = null;
        $specialites_prat = [];

        // Récupérer tous les praticiens
        $praticiens = getAllNomPraticien();

        // Récupérer les types et spécialités
        $reqTypes = 'SELECT TYP_CODE, TYP_LIBELLE FROM type_praticien ORDER BY TYP_LIBELLE';
        $resTypes = $monPdo->query($reqTypes);
        $types = $resTypes->fetchAll();

        $reqSpecialites = 'SELECT SPE_CODE, SPE_LIBELLE FROM specialite ORDER BY SPE_LIBELLE';
        $resSpecialites = $monPdo->query($reqSpecialites);
        $specialites = $resSpecialites->fetchAll();

        // 1- Le délégué demande à gérer les praticiens (affichage liste)
        if (!isset($_POST['action']) || $_POST['action'] == 'liste') {
            $mode = 'liste';
        }



        // 3- Le système affiche un écran avec les infos du praticien et ses spécialités
        if (isset($_POST['action']) && $_POST['action'] == 'editer') {
            $mode = 'editer';
            if (isset($_POST['praticien_select']) && !empty($_POST['praticien_select'])) {
                $numPrat = $_POST['praticien_select'];
                $infosPrat = getAllInformationPraticienNum($numPrat);
                
                $reqSpe = 'SELECT SPE_CODE FROM posseder WHERE PRA_NUM = ?';
                $resSpe = $monPdo->prepare($reqSpe);
                $resSpe->execute(array($numPrat));
                $specialites_prat = $resSpe->fetchAll(PDO::FETCH_COLUMN);
            }
        }

        // Créer un nouveau praticien
        if (isset($_POST['action']) && $_POST['action'] == 'creer') {
            $mode = 'editer';
            $infosPrat = array(
                'matriculepraticien' => '',
                'nom' => '',
                'prenom' => '',
                'adresse' => '',
                'codepostal' => '',
                'ville' => '',
                'coefficientdenotoriete' => '',
                'coefficientdeconfiance' => '',
                'typedepraticien' => ''
            );
            $specialites_prat = [];
        }

        // 4 et 5- Valider les informations
        if (isset($_POST['action']) && $_POST['action'] == 'valider') {
            $num = $_POST['pranum'] ?? '';
            $nom = $_POST['pranom'] ?? '';
            $prenom = $_POST['praprenom'] ?? '';
            $adresse = $_POST['praadresse'] ?? '';
            $cp = $_POST['pracp'] ?? '';
            $ville = $_POST['praville'] ?? '';
            $notoriete = $_POST['pracoefnotoriete'] ?? '';
            $confiance = $_POST['pracoefconfiance'] ?? '';
            $type = $_POST['typcode'] ?? '';
            $specialites_sel = isset($_POST['specialites']) ? $_POST['specialites'] : [];

            // Validation
            if (empty($nom)) {
                $errors[] = "Le nom du praticien est obligatoire";
            }
            if (empty($prenom)) {
                $errors[] = "Le prénom du praticien est obligatoire";
            }
            if (empty($type)) {
                $errors[] = "Le type de praticien est obligatoire";
            }

            // Exceptions: 2-a Pas de spécialité sélectionnée
            if (empty($specialites_sel)) {
                if (!isset($_POST['confirmer_sans_specialite'])) {
                    $errors[] = "Aucune spécialité sélectionnée. Veuillez sélectionner au moins une spécialité ou confirmer.";
                }
            }

            if (empty($errors)) {
                try {
                    if (empty($num)) {
                        // Créer un nouveau praticien
                        $reqInsert = 'INSERT INTO praticien (PRA_NOM, PRA_PRENOM, PRA_ADRESSE, PRA_CP, PRA_VILLE, PRA_COEFNOTORIETE, PRA_COEFCONFIANCE, TYP_CODE) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
                        $resInsert = $monPdo->prepare($reqInsert);
                        $resInsert->execute(array($nom, $prenom, $adresse, $cp, $ville, $notoriete, $confiance, $type));
                        $num = $monPdo->lastInsertId();
                        $message = "Praticien créé avec succès";
                    } else {
                        // Modifier un praticien existant
                        $reqUpdate = 'UPDATE praticien SET PRA_NOM=?, PRA_PRENOM=?, PRA_ADRESSE=?, PRA_CP=?, PRA_VILLE=?, PRA_COEFNOTORIETE=?, PRA_COEFCONFIANCE=?, TYP_CODE=? 
                                     WHERE PRA_NUM=?';
                        $resUpdate = $monPdo->prepare($reqUpdate);
                        $resUpdate->execute(array($nom, $prenom, $adresse, $cp, $ville, $notoriete, $confiance, $type, $num));
                        $message = "Praticien modifié avec succès";
                    }

                    // Gérer les spécialités
                    $reqDeleteSpe = 'DELETE FROM posseder WHERE PRA_NUM = ?';
                    $resDeleteSpe = $monPdo->prepare($reqDeleteSpe);
                    $resDeleteSpe->execute(array($num));

                    foreach ($specialites_sel as $spe) {
                        $reqInsertSpe = 'INSERT INTO posseder (PRA_NUM, SPE_CODE, POS_DIPLOME, POS_COEFPRESCRIPTIO) VALUES (?, ?, ?, ?)';
                        $resInsertSpe = $monPdo->prepare($reqInsertSpe);
                        $resInsertSpe->execute(array($num, $spe, '', 0));
                    }

                    $mode = 'liste';
                    $infosPrat = null;
                } catch (PDOException $e) {
                    $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
                    $mode = 'editer';
                    $infosPrat = array(
                        'matriculepraticien' => $num,
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'adresse' => $adresse,
                        'codepostal' => $cp,
                        'ville' => $ville,
                        'coefficientdenotoriete' => $notoriete,
                        'coefficientdeconfiance' => $confiance,
                        'typedepraticien' => $type
                    );
                    $specialites_prat = $specialites_sel;
                }
            } else {
                $mode = 'editer';
                $infosPrat = array(
                    'matriculepraticien' => $num,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'adresse' => $adresse,
                    'codepostal' => $cp,
                    'ville' => $ville,
                    'coefficientdenotoriete' => $notoriete,
                    'coefficientdeconfiance' => $confiance,
                    'typedepraticien' => $type
                );
                $specialites_prat = $specialites_sel;
            }
        }
        ?>

        <div class="row align-items-center justify-content-center mt-5">
            <!-- Affichage des messages -->
            <?php if (!empty($message)): ?>
                <div class="col-12">
                    <div class="alert alert-success text-center" role="alert">
                        <?= $message ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="col-12">
                    <div class="alert alert-danger" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <p><?= $error ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- MODE LISTE: Sélection du praticien -->
            <?php if ($mode == 'liste'): ?>
                <div class="col-12 col-lg-8">
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <h3 class="card-title mb-4">Sélectionner un praticien</h3>
                            <form method="post" action="index.php?uc=praticien&action=modif" class="formulaire-recherche">
                                <input type="hidden" name="form_action" value="editer">
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="praticien_select">Liste des praticiens du secteur :</label>
                                    <select name="praticien_select" id="praticien_select" class="form-select mt-2" required>
                                        <option value="">-- Choisissez un praticien --</option>
                                        <?php
                                        foreach ($praticiens as $prat) {
                                            echo '<option value="' . $prat['PRA_NUM'] . '">' . 
                                                 $prat['PRA_NUM'] . ' - ' . $prat['PRA_NOM'] . ' ' . $prat['PRA_PRENOM'] . 
                                                 '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning text-light">
                                        Modifier
                                    </button>
                                </div>
                            </form>
                            <form method="post" action="index.php?uc=praticien&action=modif" class="mt-3">
                                <input type="hidden" name="form_action" value="creer">
                                <button type="submit" class="btn btn-success">
                                    Créer un nouveau praticien
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- MODE EDITER: Formulaire de modification/création -->
            <?php if ($mode == 'editer' && $infosPrat): ?>
                <div class="col-12 col-lg-8">
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <h3 class="card-title mb-4">
                                <?= empty($infosPrat['matriculepraticien']) ? 'Créer un nouveau praticien' : 'Modifier le praticien' ?>
                            </h3>
                            <form method="post" action="index.php?uc=praticien&action=modif" class="formulaire-praticien">
                                <input type="hidden" name="form_action" value="valider">
                                <input type="hidden" name="pranum" value="<?= htmlspecialchars($infosPrat['matriculepraticien'] ?? '') ?>">

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="pranom">Nom * :</label>
                                    <input type="text" name="pranom" id="pranom" class="form-control" 
                                           value="<?= htmlspecialchars($infosPrat['nom'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="praprenom">Prénom * :</label>
                                    <input type="text" name="praprenom" id="praprenom" class="form-control" 
                                           value="<?= htmlspecialchars($infosPrat['prenom'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="praadresse">Adresse :</label>
                                    <input type="text" name="praadresse" id="praadresse" class="form-control" 
                                           value="<?= htmlspecialchars($infosPrat['adresse'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="pracp">Code postal :</label>
                                    <input type="text" name="pracp" id="pracp" class="form-control" 
                                           value="<?= htmlspecialchars($infosPrat['codepostal'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="praville">Ville :</label>
                                    <input type="text" name="praville" id="praville" class="form-control" 
                                           value="<?= htmlspecialchars($infosPrat['ville'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="pracoefnotoriete">Coefficient notoriété :</label>
                                    <input type="number" name="pracoefnotoriete" id="pracoefnotoriete" class="form-control" 
                                           step="0.01" value="<?= htmlspecialchars($infosPrat['coefficientdenotoriete'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="pracoefconfiance">Coefficient confiance :</label>
                                    <input type="number" name="pracoefconfiance" id="pracoefconfiance" class="form-control" 
                                           step="0.01" value="<?= htmlspecialchars($infosPrat['coefficientdeconfiance'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="typcode">Type de praticien * :</label>
                                    <select name="typcode" id="typcode" class="form-select" required>
                                        <option value="">-- Sélectionnez un type --</option>
                                        <?php
                                        foreach ($types as $type) {
                                            $selected = (!empty($infosPrat['typedepraticien']) && $infosPrat['typedepraticien'] == $type['TYP_LIBELLE']) ? 'selected' : '';
                                            echo '<option value="' . $type['TYP_CODE'] . '" ' . $selected . '>' . 
                                                 htmlspecialchars($type['TYP_LIBELLE']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Spécialités :</label>
                                    <div class="border p-3 rounded">
                                        <?php
                                        foreach ($specialites as $spe) {
                                            $checked = in_array($spe['SPE_CODE'], $specialites_prat) ? 'checked' : '';
                                            echo '<div class="form-check">';
                                            echo '<input class="form-check-input" type="checkbox" name="specialites[]" id="spe_' . 
                                                 $spe['SPE_CODE'] . '" value="' . $spe['SPE_CODE'] . '" ' . $checked . '>';
                                            echo '<label class="form-check-label" for="spe_' . $spe['SPE_CODE'] . '">';
                                            echo htmlspecialchars($spe['SPE_LIBELLE']);
                                            echo '</label></div>';
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Valider</button>
                                    <?php if (empty($specialites_prat)): ?>
                                        <button type="submit" name="confirmer_sans_specialite" value="1" class="btn btn-warning">
                                            Confirmer sans spécialité
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                            <form method="post" action="index.php?uc=praticien&action=modif" class="mt-3">
                                <input type="hidden" name="form_action" value="liste">
                                <button type="submit" class="btn btn-secondary">Annuler</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
