function toggleMotifAutre() {
    var motifSelect = document.getElementById('motif');
    var container = document.getElementById('motifAutreContainer');
    var inputAutre = document.getElementById('motif_autre');
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
    let echantillonCount = 0;

    function reindexEchantillons() {
        echantillonCount = 0;
        const rows = container.querySelectorAll('.echantillon-row');
        rows.forEach((row, index) => {
            const newIndex = index + 1;
            echantillonCount = newIndex;
            row.querySelector('.echantillon-index').textContent = newIndex;
            row.querySelector('.echantillon-medoc').name = `echantillon_medoc_${newIndex}`;
            row.querySelector('.echantillon-qte').name = `echantillon_qte_${newIndex}`;

            const deleteButton = row.querySelector('.supprimer-echantillon');
            deleteButton.style.display = (rows.length > 1) ? 'block' : 'none';
        });
        addButton.disabled = (echantillonCount >= MAX_ECHANTILLONS);
    }

    function addEchantillonRow() {
        if (echantillonCount >= MAX_ECHANTILLONS) return;
        const clone = template.content.cloneNode(true).querySelector('.echantillon-row');

        clone.querySelector('.supprimer-echantillon').addEventListener('click', function () {
            clone.remove();
            reindexEchantillons();
        });

        container.appendChild(clone);
        reindexEchantillons();
    }

    addButton.addEventListener('click', addEchantillonRow);

    if (echantillonCount === 0) {
        addEchantillonRow();
    }
});
