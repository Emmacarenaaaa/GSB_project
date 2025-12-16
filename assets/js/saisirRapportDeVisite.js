function toggleMotifAutre() {
    var motifSelect = document.getElementById('motif');
    var container = document.getElementById('motifAutreContainer');
    var inputAutre = document.getElementById('motif_autre');

    if (motifSelect.value == '4') {
        container.style.display = 'block';
        inputAutre.required = true;
    } else {
        container.style.display = 'none';
        inputAutre.required = false;
        inputAutre.value = '';
    }
}

function validerFormulaire() {
    // Récupérer les valeurs
    var praticien = document.getElementById('praticien').value;
    var dateVisite = document.getElementById('dateVisite').value;
    var motif = document.getElementById('motif').value;
    var bilan = document.getElementById('bilan').value.trim();
    var etat = document.getElementById('etat').value;

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

    // Vérifications des champs obligatoires
    if (praticien === '') {
        alert('Veuillez sélectionner un praticien.');
        document.getElementById('praticien').focus();
        return false;
    }

    if (dateVisite === '') {
        alert('Veuillez saisir une date de visite.');
        document.getElementById('dateVisite').focus();
        return false;
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

    // BLOCAGE STRICT : La date ne peut PAS être dans le futur
    var dateSelectionnee = new Date(dateVisite);
    var aujourdhui = new Date();
    aujourdhui.setHours(0, 0, 0, 0);

    if (dateSelectionnee > aujourdhui) {
        alert('ERREUR : La date de visite ne peut pas être dans le futur.\nVeuillez sélectionner une date antérieure ou égale à aujourd\'hui.');
        document.getElementById('dateVisite').focus();
        return false;
    }

    // Récupérer les textes pour l'affichage
    var praticienTexte = document.getElementById('praticien').options[document.getElementById('praticien').selectedIndex].text;
    var motifTexte = document.getElementById('motif').options[document.getElementById('motif').selectedIndex].text;
    var etatTexte = document.getElementById('etat').options[document.getElementById('etat').selectedIndex].text;

    // Message de confirmation détaillé
    var message = 'CONFIRMEZ-VOUS L\'ENREGISTREMENT DE CE RAPPORT ?\n\n';
    message += '=====================================\n';
    message += 'Praticien : ' + praticienTexte + '\n';
    message += 'Date de visite : ' + dateVisite + '\n';
    message += 'Motif : ' + motifTexte + '\n';

    if (motif == '4') {
        message += 'Precision : ' + document.getElementById('motif_autre').value + '\n';
    }

    var medoc1Select = document.getElementById('medoc1');
    if (medoc1Select.value !== '') {
        message += 'Medicament 1 : ' + medoc1Select.options[medoc1Select.selectedIndex].text + '\n';
    }

    var medoc2Select = document.getElementById('medoc2');
    if (medoc2Select.value !== '') {
        message += 'Medicament 2 : ' + medoc2Select.options[medoc2Select.selectedIndex].text + '\n';
    }

    var remplacantSelect = document.getElementById('numRemplacant');
    if (remplacantSelect.value !== '') {
        message += 'Remplacant : ' + remplacantSelect.options[remplacantSelect.selectedIndex].text + '\n';
    }

    message += 'Etat : ' + etatTexte + '\n';
    message += '=====================================\n\n';
    message += 'Cliquez sur OK pour enregistrer.';

    // Retourner true si confirmé, false sinon
    return confirm(message);
}
