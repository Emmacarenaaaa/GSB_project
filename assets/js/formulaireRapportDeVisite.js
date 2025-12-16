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
            alert("La quantité pour l'échantillon " + (i + 1) + " doit être supérieure à 0.");
            return false;
        }
        if (!medoc && qte > 0) {
            alert("Veuillez sélectionner un médicament pour l'échantillon " + (i + 1) + ".");
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
});
