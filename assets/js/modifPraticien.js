document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#formPraticien');
    if (form) {
        form.addEventListener('submit', function (e) {
            // Check if we are validating (form_action usually hidden input, but button click triggers submit)
            // Actually the form has hidden input name="form_action" value="valider"

            const checked = document.querySelectorAll('input[name="specialites[]"]:checked');
            if (checked.length === 0) {
                if (!confirm("Vous n'avez sélectionné aucune spécialité. Voulez-vous continuer (Aucune spécialité sera affichée) ?")) {
                    e.preventDefault();
                }
            }
        });
    }
});
