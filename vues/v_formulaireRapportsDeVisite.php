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
        <!-- Affichage des messages d'erreur si présents -->
        <?php if (isset($_SESSION['erreur']) && $_SESSION['erreur']) {
          echo '<p class="alert alert-danger text-center w-100">Un problème est survenu lors de la selection du rapport de visite</p>';
          $_SESSION['erreur'] = false;
        } ?>

        <!-- 
            FORMULAIRE DE FILTRES 
            Permet de restreindre l'affichage des rapports par date et par praticien.
            Les options de filtrage sont : Date de début, Date de fin, et Praticien.
        -->
        <form id="formFiltre" action="index.php?uc=rapportvisite&action=voirrapport" method="post" class="formulaire-recherche col-12 m-0 mb-4 p-3 border rounded">
          <h5 class="mb-3">Filtrer les rapports :</h5>
          
          <!-- Filtre : Date de début -->
          <label for="dateDebut">Date de début (optionnel) :</label>
          <input type="date" name="dateDebut" id="dateDebut" class="form-control mb-3" 
                 value="<?php echo isset($_POST['dateDebut']) ? htmlspecialchars($_POST['dateDebut']) : ''; ?>">
          
          <!-- Filtre : Date de fin -->
          <label for="dateFin">Date de fin (optionnel) :</label>
          <input type="date" name="dateFin" id="dateFin" class="form-control mb-3"
                 value="<?php echo isset($_POST['dateFin']) ? htmlspecialchars($_POST['dateFin']) : ''; ?>">
          
          <!-- Filtre : Praticien (Liste filtrée par région dans le contrôleur) -->
          <label for="praticienFiltre">Praticien (optionnel) :</label>
          <select name="praticienFiltre" id="praticienFiltre" class="form-select mb-3">
            <option value="">-- Tous les praticiens --</option>
            <?php 
            // On conserve la sélection de l'utilisateur après soumission
            $praticienSelectionne = isset($_POST['praticienFiltre']) ? $_POST['praticienFiltre'] : '';
            
            // Itération sur la liste des praticiens (passée par le contrôleur)
            foreach($praticiens as $praticien) {
              $selected = ($praticienSelectionne == $praticien['PRA_NUM']) ? 'selected' : '';
              echo '<option value="'.$praticien['PRA_NUM'].'" '.$selected.'>'.htmlspecialchars($praticien['PRA_NOM'].' '.$praticien['PRA_PRENOM']).'</option>';
            } 
            ?>
          </select>

          <!-- Boutons d'action : Filtrer et Réinitialiser -->
          <div class="d-flex gap-2">
            <input class="btn btn-primary flex-grow-1" type="submit" value="Filtrer">
            <a href="index.php?uc=rapportvisite&action=voirrapport" class="btn btn-secondary">Réinitialiser</a>
          </div>

        </form>

    

      </div>
          <!-- FORMULAIRE D'AFFICHAGE -->


        <!-- 
            TABLEAU D'AFFICHAGE DES RAPPORTS
            N'apparaît que si l'utilisateur est habilité et qu'il n'est pas responsable (sauf exception de logic)
        -->
        <?php if (isset($_SESSION['hab_id']) && (!empty($result) || $_SESSION['hab_id'] != 3)): ?>
        <div class="col-12 m-0 mt-4">
          <h5 class="titre-formulaire">Historique des rapports :</h5>
          
          <?php if (empty($result)): ?>
            <!-- Message si aucun résultat trouvé -->
            <p class="alert alert-warning mt-3">Aucun rapport trouvé avec ces critères.</p>
          <?php else: ?>
            <!-- Table des résultats -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Num</th>
                            <th>Visiteur</th>
                            <th>Praticien</th>
                            <th>Motif</th>
                            <th>Date</th>
                            <th>Médicaments</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($result as $rapport): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rapport['RAP_NUM']); ?></td>
                            <td><?php echo htmlspecialchars($rapport['COL_NOM'] . ' ' . $rapport['COL_PRENOM']); ?></td>
                            <td><?php echo htmlspecialchars($rapport['PRA_NOM'] . ' ' . $rapport['PRA_PRENOM']); ?></td>
                            <td>
                                <?php 
                                    echo htmlspecialchars($rapport['MO_LIBELLE']);
                                    if (!empty($rapport['RAP_MOTIF_AUTRE'])) {
                                        echo ' (' . htmlspecialchars($rapport['RAP_MOTIF_AUTRE']) . ')';
                                    }
                                ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($rapport['RAP_DATEVISITE'])); ?></td>
                            <td>
                                <?php 
                                    $meds = [];
                                    if (!empty($rapport['MED1'])) $meds[] = $rapport['MED1'];
                                    if (!empty($rapport['MED2'])) $meds[] = $rapport['MED2'];
                                    echo htmlspecialchars(implode(', ', $meds));
                                ?>
                            </td>
                            <td>
                                <a href="index.php?uc=rapportvisite&action=afficherrapport&rapports=<?php echo $rapport['RAP_NUM']; ?>" class="btn btn-sm btn-info text-white">Voir</a>
                            </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted"><?php echo count($result); ?> rapport(s) trouvé(s)</small>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <div>
        </div>
      
      <!-- Boutons pour créer un nouveau rapport ou voir les brouillons -->
      <?php if (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] != 3): ?>
<div class="test col-12 col-sm-8 col-lg-6 col-xl-5 col-xxl-4 py-lg-5">
  <input class="btn btn-info text-light valider" type="button" value="Remplir un rapport" onclick="window.location.href='index.php?uc=rapportvisite&action=saisirrapport'">
  <input class="btn btn-warning text-light valider mt-2" type="button" value="Mes rapports brouillon" onclick="window.location.href='index.php?uc=rapportvisite&action=mesRapportsBrouillon'">
  </div>
<?php endif; ?>
    </div>


  </div>

</section>
