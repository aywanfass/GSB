/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Other/javascript.js to edit this template
 */


(function () {
    var form = document.getElementById('chargerFicheForm');
    if (!form) return;

    var selectVisiteur = form.querySelector('select[name="visiteur"]');
    var selectMois = form.querySelector('select[name="mois"]');

    function submitForm() {
        form.submit();
    }

    if (selectVisiteur) {
        selectVisiteur.addEventListener('change', submitForm, { passive: true });
    }
    if (selectMois) {
        selectMois.addEventListener('change', submitForm, { passive: true });
    }
})();