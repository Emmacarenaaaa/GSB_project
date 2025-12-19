<head>
    <title>Projet GSB</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/boxicon.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/gsb.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>
    <nav id="main_nav" class="navbar navbar-expand-lg navbar-light bg-white shadow">
        <div class="menuCont container">
            <a class="navbar-brand h1 my-2" href="index.php?uc=accueil">
                <span class="text-dark h4 fw-bold">Projet</span> <span class="text-info h4 fw-bold">GSB</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbar-toggler-success" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="align-self-center collapse navbar-collapse flex-fill  d-lg-flex justify-content-lg-between"
                id="navbar-toggler-success">
                <div class="flex-fill d-flex justify-content-end">
                    <ul class="nav navbar-nav d-flex justify-content-between mx-xl-5 text-center text-dark">
                        <li class="nav-item ">
                            <a class="nav-link btn-outline-info rounded-pill px-3 fw-bold d-flex align-items-center"
                                href="index.php?uc=accueil">
                                <img src="assets/img/accueil.png" alt=""
                                    style="width: 20px; height: 20px; margin-right: 8px;"> Accueil
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link btn-outline-info rounded-pill px-3 fw-bold"
                                href="index.php?uc=medicaments&action=formulairemedoc">Médicaments</a>
                        </li>

                        <!-- Menu Praticiens -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn-outline-info rounded-pill px-3 fw-bold" href="#"
                                id="praticienDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Praticiens
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="praticienDropdown">
                                <li><a class="dropdown-item"
                                        href="index.php?uc=praticien&action=formulaireprat">Consulter</a></li>
                                <?php if (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] != 1): ?>
                                    <li><a class="dropdown-item" href="index.php?uc=praticien&action=modif">Gérer</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Menu Rapports -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn-outline-info rounded-pill px-3 fw-bold" href="#"
                                id="rapportDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Rapports
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="rapportDropdown">
                                <li><a class="dropdown-item"
                                        href="index.php?uc=rapportvisite&action=mesRapportsClos">Consulter mes
                                        rapports</a></li>
                                <li><a class="dropdown-item"
                                        href="index.php?uc=rapportvisite&action=voirrapport">Historique des rapports</a>
                                </li>

                                <?php if (isset($_SESSION['hab_id']) && $_SESSION['hab_id'] != 3): ?>
                                    <li><a class="dropdown-item"
                                            href="index.php?uc=rapportvisite&action=saisirrapport">Nouveau Rapport</a></li>
                                    <li><a class="dropdown-item"
                                            href="index.php?uc=rapportvisite&action=mesRapportsBrouillon">Mes Brouillons</a>
                                    </li>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['hab_id']) && ($_SESSION['hab_id'] == 2 || $_SESSION['hab_id'] == 3)): ?>
                                    <li><a class="dropdown-item"
                                            href="index.php?uc=rapportvisite&action=nouveauxRapportsRegion">Consulter
                                            nouveaux rapports</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <li class="nav-item ">
                            <a class="nav-link btn-outline-info rounded-pill px-3 fw-bold d-flex align-items-center"
                                href="index.php?uc=connexion&action=profil">
                                <img src="assets/img/profil.png" alt=""
                                    style="width: 20px; height: 20px; margin-right: 8px;"> Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-outline-info rounded-pill px-3 fw-bold d-flex align-items-center"
                                href="index.php?uc=connexion&action=deconnexion"
                                onclick="return confirm('Voulez-vous vraiment vous déconnecter ?');">
                                <img src="assets/img/deco.png" alt=""
                                    style="width: 20px; height: 20px; margin-right: 8px;"> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>