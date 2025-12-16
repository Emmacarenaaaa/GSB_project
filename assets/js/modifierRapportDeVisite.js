function toggleMotifAutre() {
    var motifSelect = document.getElementById('motif');
    var container = document.getElementById('motifAutreContainer');
    var inputAutre = document.getElementById('motif_autre');

    if (motifSelect.value == '4') { // 4 est supposé être le code pour "Autre" ou similaire, à vérifier avec la BDD
        container.style.display = 'block';
        inputAutre.required = true;
    } else {
        container.style.display = 'none';
        inputAutre.required = false;
    }
}

// Initialiser l'affichage du motif autre au chargement
document.addEventListener('DOMContentLoaded', function () {
    toggleMotifAutre();
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

    return confirm('Confirmez-vous la modification de ce rapport ?');
}
