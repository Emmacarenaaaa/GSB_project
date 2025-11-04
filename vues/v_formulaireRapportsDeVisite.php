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
        <?php if ($_SESSION['erreur']) {
          echo '<p class="alert alert-danger text-center w-100">Un problème est survenu lors de la selection du rapport de visite</p>';
          $_SESSION['erreur'] = false;
        } ?>
        <form action="index.php?uc=rapportvisite&action=afficherrapport" method="post" class="formulaire-recherche col-12 m-0">
          <label class="titre-formulaire" for="listerapport">Rapports disponibles :</label>
          <select required name="rapports" class="form-select mt-3">
            <option value class="text-center">- Choisissez un rapport -</option>
            <?php
           foreach ($result as $key) {
            // Formatage de la date en jj/mm/aaaa
            $dateVisite = date('d/m/Y', strtotime($key['RAP_DATEVISITE']));
            $label = $key['RAP_NUM'] . ' - ' . $dateVisite . ' - ' . $key['COL_NOM'] . ' ' . $key['COL_PRENOM'];
              echo '<option value="' . htmlspecialchars($key['RAP_NUM']) . '" class="form-control">' . htmlspecialchars($label) . '</option>';
            }
            ?>
          </select>
          <input class="btn btn-info text-light valider" type="submit" value="Afficher les informations">
        </form>
      </div>
    </div>
  </div>
</section>