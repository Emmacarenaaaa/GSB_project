<div class="container">
    <div class="structure-hero pt-lg-5 pt-4">
        <h1 class="titre text-center">Sélection des rapports de visite</h1>
        <p class="text text-center">
            Veuillez sélectionner les critères de recherche pour afficher l'historique des rapports.
        </p>
    </div>
    <div class="row align-items-center justify-content-center">
        <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5">
            <img class="img-fluid size-img-page" src="assets/img/rapport.jpg">
        </div>
        <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5 py-3">
            <!-- Affichage des messages d'erreur si présents -->
            <?php if (isset($_SESSION['erreur']) && $_SESSION['erreur']) {
                echo '<p class="alert alert-danger text-center w-100">Un problème est survenu lors de la selection du rapport de visite</p>';
                $_SESSION['erreur'] = false;
            } ?>

            <!-- 
            FORMULAIRE DE FILTRES 
            Permet de restreindre l'affichage des rapports par date et par praticien.
            Action pointe vers 'validerSelection' pour afficher la liste ensuite.
        -->
            <form id="formFiltre" action="index.php?uc=rapportvisite&action=validerSelection" method="post"
                class="formulaire-recherche col-12 m-0 mb-4 p-3 border rounded">
                <h5 class="mb-3">Filtrer les rapports :</h5>
                <p class="small text-muted mb-3">Les dates sont obligatoires.</p>

                <!-- Filtre : Date de début -->
                <label for="dateDebut">Date de début :</label>
                <input type="date" name="dateDebut" id="dateDebut" class="form-control mb-3" required
                    value="<?php echo isset($_POST['dateDebut']) ? htmlspecialchars($_POST['dateDebut']) : ''; ?>">

                <!-- Filtre : Date de fin -->
                <label for="dateFin">Date de fin :</label>
                <input type="date" name="dateFin" id="dateFin" class="form-control mb-3" required
                    value="<?php echo isset($_POST['dateFin']) ? htmlspecialchars($_POST['dateFin']) : ''; ?>">

                <?php if (isset($lesVisiteurs)): ?>
                    <!-- Filtre : Visiteur (Pour Délégués et Responsables) -->
                    <label for="visiteurFiltre">Visiteur (optionnel) :</label>
                    <select name="visiteurFiltre" id="visiteurFiltre" class="form-select mb-3">
                        <option value="">-- Choisir un visiteur --</option>
                        <?php
                        $visiteurSelectionne = isset($_POST['visiteurFiltre']) ? $_POST['visiteurFiltre'] : '';
                        foreach ($lesVisiteurs as $visiteur) {
                            $selected = ($visiteurSelectionne == $visiteur['COL_MATRICULE']) ? 'selected' : '';
                            echo '<option value="' . $visiteur['COL_MATRICULE'] . '" ' . $selected . '>' . htmlspecialchars($visiteur['COL_NOM'] . ' ' . $visiteur['COL_PRENOM']) . '</option>';
                        }
                        ?>
                    </select>
                <?php else: ?>
                    <!-- Filtre : Praticien (Pour Visiteurs) -->
                    <label for="praticienFiltre">Praticien (optionnel) :</label>
                    <select name="praticienFiltre" id="praticienFiltre" class="form-select mb-3">
                        <option value="">-- Choisir un praticien --</option>
                        <?php
                        $praticienSelectionne = isset($_POST['praticienFiltre']) ? $_POST['praticienFiltre'] : '';
                        foreach ($praticiens as $praticien) {
                            $selected = ($praticienSelectionne == $praticien['PRA_NUM']) ? 'selected' : '';
                            echo '<option value="' . $praticien['PRA_NUM'] . '" ' . $selected . '>' . htmlspecialchars($praticien['PRA_NOM'] . ' ' . $praticien['PRA_PRENOM']) . '</option>';
                        }
                        ?>
                    </select>
                <?php endif; ?>

                <!-- Boutons d'action : Valider la sélection -->
                <div class="d-flex gap-2">
                    <input class="btn btn-info text-light valider flex-grow-1" type="submit"
                        value="Valider la sélection">
                </div>

            </form>

            <!-- Boutons pour créer un nouveau rapport ou voir les brouillons (dispo ici aussi pour accès rapide) -->
            <?php if (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] != 3): ?>
                <div class="mt-4">
                    <input class="btn btn-info text-light valider w-100" type="button" value="Remplir un nouveau rapport"
                        onclick="window.location.href='index.php?uc=rapportvisite&action=saisirrapport'">
                    <input class="btn btn-warning text-light valider mt-2 w-100" type="button"
                        value="Mes rapports brouillon"
                        onclick="window.location.href='index.php?uc=rapportvisite&action=mesRapportsBrouillon'">
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>