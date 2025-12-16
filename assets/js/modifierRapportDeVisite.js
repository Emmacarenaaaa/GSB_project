function toggleMotifAutre() {
    var motifSelect = document.getElementById('motif');
    var container = document.getElementById('motifAutreContainer');
    var inputAutre = document.getElementById('motif_autre');
    // Check if element exists before accessing properties (safety)
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

// Initialiser l'affichage du motif autre et le chargement des échantillons au chargement
document.addEventListener('DOMContentLoaded', function () {
    toggleMotifAutre();

    // --- LOGIQUE ECHANTILLONS ---
    const container = document.getElementById('echantillons-container');
    const template = document.getElementById('echantillon-template');
    const addButton = document.getElementById('ajouter-echantillon');
    const MAX_ECHANTILLONS = 10;

    // Si les éléments n'existent pas dans la page (cas d'erreur), on sort
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
        // data peut être { medocId: '...', qte: 5 }
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

    // Event Listener pour le bouton ajouter
    addButton.addEventListener('click', function () {
        addEchantillonRow();
    });


    // Chargement des données initiales PHP -> JS
    const echantillonsData = window.ECHANTILLONS_INITIAUX || [];

    if (echantillonsData.length > 0) {
        echantillonsData.forEach(function (item) {
            addEchantillonRow({
                medocId: item.MED_DEPOTLEGAL,
                qte: item.QTE
            });
        });
    } else {
        // Par défaut, on ne met rien ou une ligne vide selon préférence. 
        // Mettons une ligne vide si on veut inciter à saisir
        addEchantillonRow();
    }
});

function validerFormulaire() {
    // Récupérer les valeurs
    var motif = document.getElementById('motif').value;
    var bilan = document.getElementById('bilan').value.trim();

    // Vérifier le champ "Motif autre" si motif = 4
    if (motif == '4') {
        var motifAutre = document.getElementById('motif_autre').value.trim();
        if (motifAutre === '') {
            alert('Veuillez préciser le motif.');
            document.getElementById('motif_autre').focus();
            return false;
        }
        if (motifAutre.length > 50) {
            alert('Le motif personnalisé ne peut pas dépasser 50 caractères.');
            document.getElementById('motif_autre').focus();
            return false;
        }
    }

    if (motif === '') {
        alert('Veuillez sélectionner un motif.');
        document.getElementById('motif').focus();
        return false;
    }

    if (bilan === '') {
        alert('Veuillez remplir le bilan.');
        document.getElementById('bilan').focus();
        return false;
    }

    if (bilan.length > 255) {
        alert('Le bilan ne peut pas dépasser 255 caractères.');
        document.getElementById('bilan').focus();
        return false;
    }

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

    return confirm('Confirmez-vous la modification de ce rapport ?');
}
