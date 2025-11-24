<div class="container">
    <div class="structure-hero pt-lg-5 pt-4">
      <h1 class="titre text-center">Mes rapports brouillon</h1>
      <p class="text text-center">
        Liste de vos rapports en cours de rédaction (état "Nouveau").
      </p>
    </div>
    <div class="row align-items-center justify-content-center">
      <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5">
        <img class="img-fluid size-img-page" src="assets/img/rapport.jpg">
      </div>
      <div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5 py-3">
        
        <!-- FORMULAIRE D'AFFICHAGE -->
        <form action="index.php?uc=rapportvisite&action=afficherrapport" method="post" class="formulaire-recherche col-12 m-0">
          <label class="titre-formulaire" for="rapports">Rapports brouillon :</label>
          
          <?php if (empty($lesRapportsBrouillon)): ?>
            <p class="alert alert-warning mt-3">Aucun rapport brouillon trouvé.</p>
          <?php else: ?>
            <select required name="rapports" id="rapports" class="form-select mt-3">
              <option value="">- Choisissez un rapport -</option>
              <?php
              foreach ($lesRapportsBrouillon as $key) {
                // Formatage de la date en jj/mm/aaaa
                $dateVisite = date('d/m/Y', strtotime($key['RAP_DATEVISITE']));
                $label = $key['RAP_NUM'] . ' - ' . $dateVisite . ' - ' . $key['COL_NOM'] . ' ' . $key['COL_PRENOM'];
                echo '<option value="' . htmlspecialchars($key['RAP_NUM']) . '">' . htmlspecialchars($label) . '</option>';
              }
              ?>
            </select>
            <small class="text-muted"><?php echo count($lesRapportsBrouillon); ?> rapport(s) trouvé(s)</small>
            <input class="btn btn-info text-light valider mt-3" type="submit" value="Afficher les informations">
          <?php endif; ?>
        </form>
        
        <div class="mt-4">
            <a href="index.php?uc=rapportvisite&action=voirrapport" class="btn btn-secondary">Retour aux rapports</a>
        </div>

      </div>
    </div>
</div>
