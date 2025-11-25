<div class="container">
    <div class="structure-hero pt-lg-5 pt-4">
      <h1 class="titre text-center">Formulaire de rapports de visite</h1>
      <p class="text text-center">
        Formulaire permettant d'afficher toutes les informations
        à propos d'un rapport de visite en particulier.
      </p>
    </div>
    <div class="row align-items-center justify-content-center">
      <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5">
        <img class="img-fluid size-img-page" src="assets/img/rapport.jpg">
      </div>
      <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5 py-3">
        <?php if (isset($_SESSION['erreur']) && $_SESSION['erreur']) {
          echo '<p class="alert alert-danger text-center w-100">Un problème est survenu lors de la selection du rapport de visite</p>';
          $_SESSION['erreur'] = false;
        } ?>

        <!-- FORMULAIRE DE FILTRES -->
        <form id="formFiltre" action="index.php?uc=rapportvisite&action=voirrapport" method="post" class="formulaire-recherche col-12 m-0 mb-4 p-3 border rounded">
          <h5 class="mb-3">Filtrer les rapports :</h5>
          
          <label for="dateDebut">Date de début (optionnel) :</label>
          <input type="date" name="dateDebut" id="dateDebut" class="form-control mb-3" 
                 value="<?php echo isset($_POST['dateDebut']) ? htmlspecialchars($_POST['dateDebut']) : ''; ?>">
          
          <label for="dateFin">Date de fin (optionnel) :</label>
          <input type="date" name="dateFin" id="dateFin" class="form-control mb-3"
                 value="<?php echo isset($_POST['dateFin']) ? htmlspecialchars($_POST['dateFin']) : ''; ?>">
          
          <label for="praticienFiltre">Praticien (optionnel) :</label>
          <select name="praticienFiltre" id="praticienFiltre" class="form-select mb-3">
            <option value="">-- Tous les praticiens --</option>
            <?php 
            $praticienSelectionne = isset($_POST['praticienFiltre']) ? $_POST['praticienFiltre'] : '';
            foreach($praticiens as $praticien) {
              $selected = ($praticienSelectionne == $praticien['PRA_NUM']) ? 'selected' : '';
              echo '<option value="'.$praticien['PRA_NUM'].'" '.$selected.'>'.htmlspecialchars($praticien['PRA_NOM'].' '.$praticien['PRA_PRENOM']).'</option>';
            } 
            ?>
          </select>
          
          <div class="d-flex gap-2">
            <input class="btn btn-primary flex-grow-1" type="submit" value="Filtrer">
            <a href="index.php?uc=rapportvisite&action=voirrapport" class="btn btn-secondary">Réinitialiser</a>
          </div>

        </form>

    

      </div>
          <!-- FORMULAIRE D'AFFICHAGE -->
        <?php if (isset($_SESSION['hab_id'])): ?>
        <form action="index.php?uc=rapportvisite&action=afficherrapport" method="post" class="formulaire-recherche col-12 m-0">
          <label class="titre-formulaire" for="rapports">Rapports disponibles :</label>
          
          <?php if (empty($result)): ?>
            <p class="alert alert-warning mt-3">Aucun rapport trouvé avec ces critères.</p>
          <?php else: ?>
            <select required name="rapports" id="rapports" class="form-select mt-3">
              <option value="">- Choisissez un rapport -</option>
              <?php
              foreach ($result as $key) {
                // Formatage de la date en jj/mm/aaaa
                $dateVisite = date('d/m/Y', strtotime($key['RAP_DATEVISITE']));
                $label = $key['RAP_NUM'] . ' - ' . $dateVisite . ' - ' . $key['COL_NOM'] . ' ' . $key['COL_PRENOM'];
                echo '<option value="' . htmlspecialchars($key['RAP_NUM']) . '">' . htmlspecialchars($label) . '</option>';
              }
              ?>
            </select>
            <small class="text-muted"><?php echo count($result); ?> rapport(s) trouvé(s)</small>
            <input class="btn btn-info text-light valider mt-3" type="submit" value="Afficher les informations">
          <?php endif; ?>
        </form>
        <?php endif; ?>
        <div>
        </div>
      <?php if (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] != 3): ?>
<div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5">
  <input class="btn btn-info text-light valider" type="button" value="Remplir un rapport" onclick="window.location.href='index.php?uc=rapportvisite&action=saisirrapport'">
  <input class="btn btn-warning text-light valider mt-2" type="button" value="Mes rapports brouillon" onclick="window.location.href='index.php?uc=rapportvisite&action=mesRapportsBrouillon'">
  </div>
<?php endif; ?>
    </div>


  </div>

</section>
