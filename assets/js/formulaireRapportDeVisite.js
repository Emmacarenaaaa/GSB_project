function toggleMotifAutre() {
    var motifSelect = document.getElementById('motif');
    var container = document.getElementById('motifAutreContainer');
    var inputAutre = document.getElementById('motif_autre');

    // Safety check
    if (!motifSelect || !container || !inputAutre) return;

    var selectedText = motifSelect.options[motifSelect.selectedIndex].text;

    if (motifSelect.value == '4' || selectedText === 'Autre') {
        container.style.display = 'block';
        inputAutre.required = true;
    } else {
        container.style.display = 'none';
        inputAutre.required = false;
    }
}

function validerFormulaire() {
    // Validation des échantillons
    const rows = document.querySelectorAll('#echantillons-container .echantillon-row');
    for (let i = 0; i < rows.length; i++) {
        let row = rows[i];
        let medoc = row.querySelector('.echantillon-medoc').value;
        let qte = row.querySelector('.echantillon-qte').value;

        if (medoc && (qte <= 0 || qte == '')) {
            if (!confirm("Vous n'avez pas saisi de quantité pour l'échantillon " + (i + 1) + ". Voulez-vous confirmer l'enregistrement ?")) {
                row.querySelector('.echantillon-qte').focus();
                return false;
            }
        }
        if (!medoc && qte > 0) {
            alert("Veuillez sélectionner un médicament pour l'échantillon " + (i + 1) + ".");
            return false;
        }
    }

    // Validation des médicaments présentés
    var medoc1 = document.getElementById('medoc1').value;
    var medoc2 = document.getElementById('medoc2').value;

    if (medoc1 === '' && medoc2 === '') {
        if (!confirm("Aucun médicament présenté n'a été saisi. Voulez-vous confirmer l'enregistrement ?")) {
            return false;
        }
    }

    return confirm('Confirmez-vous l\'enregistrement ?');
}

document.addEventListener('DOMContentLoaded', function () {
    // --- 1. Logique du Motif Autre ---
    toggleMotifAutre();

    // --- 2. Logique des Échantillons Dynamiques ---
    const container = document.getElementById('echantillons-container');
    const template = document.getElementById('echantillon-template');
    const addButton = document.getElementById('ajouter-echantillon');
    const MAX_ECHANTILLONS = 10;

    if (!container || !template || !addButton) return;

    function reindexEchantillons() {
        const rows = container.querySelectorAll('.echantillon-row');
        rows.forEach((row, index) => {
            const newIndex = index + 1;
            row.querySelector('.echantillon-index').textContent = newIndex;
            row.querySelector('.echantillon-medoc').name = `echantillon_medoc_${newIndex}`;
            row.querySelector('.echantillon-qte').name = `echantillon_qte_${newIndex}`;

            const deleteButton = row.querySelector('.supprimer-echantillon');
            deleteButton.style.display = (rows.length > 1) ? 'block' : 'none';
        });
        addButton.disabled = (rows.length >= MAX_ECHANTILLONS);
    }

    function addEchantillonRow(data = null) {
        const currentCount = container.querySelectorAll('.echantillon-row').length;
        if (currentCount >= MAX_ECHANTILLONS) return;

        const clone = template.content.cloneNode(true).querySelector('.echantillon-row');

        // Si on a des données (chargement initial)
        if (data) {
            const medocSelect = clone.querySelector('.echantillon-medoc');
            const qteInput = clone.querySelector('.echantillon-qte');

            if (medocSelect) medocSelect.value = data.medocId;
            if (qteInput) qteInput.value = data.qte;
        }

        clone.querySelector('.supprimer-echantillon').addEventListener('click', function () {
            clone.remove();
            reindexEchantillons();
        });

        container.appendChild(clone);
        reindexEchantillons();
    }

    addButton.addEventListener('click', function () {
        addEchantillonRow();
    });

    // Chargement des données initiales PHP -> JS
    // ECHANTILLONS_INITIAUX est défini dans la vue PHP
    var echantillonsData = [];
    if (typeof ECHANTILLONS_INITIAUX !== 'undefined') {
        echantillonsData = ECHANTILLONS_INITIAUX;
    } else if (window.ECHANTILLONS_INITIAUX) {
        echantillonsData = window.ECHANTILLONS_INITIAUX;
    }

    if (echantillonsData.length > 0) {
        echantillonsData.forEach(function (item) {
            // Check for case variations just in case
            var id = item.MED_DEPOTLEGAL || item.med_depotlegal || item.Med_Depotlegal;
            var qty = item.QTE || item.qte || item.Qte;

            if (id) {
                addEchantillonRow({
                    medocId: id,
                    qte: qty
                });
            }
        });
    } else {
        // Ajouter une ligne vide par défaut en création ou si pas d'échantillons
        addEchantillonRow();
    }

    // --- 3. Logique Coefficient Confiance ---
    const praSelect = document.getElementById('praticien');
    const rempSelect = document.getElementById('numRemplacant');
    const coefInput = document.getElementById('coefConfiance');
    const oldCoefDisplay = document.getElementById('oldCoefDisplay');

    function updateCoef() {
        if (!praSelect || !coefInput) return;

        let selectedCoef = '';
        let source = '';

        // Priorité au remplaçant si sélectionné
        if (rempSelect && rempSelect.value !== '') {
            const option = rempSelect.options[rempSelect.selectedIndex];
            selectedCoef = option.getAttribute('data-coef');
            source = ' (Remplaçant)';
        } else {
            const option = praSelect.options[praSelect.selectedIndex];
            if (option) selectedCoef = option.getAttribute('data-coef');
            source = ' (Praticien)';
        }

        // On met à jour la valeur
        // Si null en base, on met vide.
        coefInput.value = selectedCoef ? selectedCoef : '';

        if (oldCoefDisplay) {
            oldCoefDisplay.title = selectedCoef
                ? "Valeur actuelle en base : " + selectedCoef + source
                : "Pas de valeur en base" + source;
        }
    }

    if (praSelect) {
        praSelect.addEventListener('change', updateCoef);
        // Initialiser au chargement (pour modification ou pré-remplissage)
        setTimeout(updateCoef, 100);
    }

    if (rempSelect) {
        rempSelect.addEventListener('change', updateCoef);
    }
});
