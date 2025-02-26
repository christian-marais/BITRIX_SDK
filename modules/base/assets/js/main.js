// Fonction pour afficher une alerte
function showAlert(message, type = 'info') {
    const modal = document.getElementById('alertModal');
    const modalBody = document.getElementById('alertModalBody');
    const modalTitle = document.getElementById('alertModalLabel');

    // Définir le type d'alerte
    let alertClass = 'text-primary'; // défaut
    let title = 'Information';
    switch(type) {
        case 'success':
            alertClass = 'text-success';
            title = 'Succès';
            break;
        case 'warning':
            alertClass = 'text-warning';
            title = 'Avertissement';
            break;
        case 'danger':
            alertClass = 'text-danger';
            title = 'Erreur';
            break;
        case 'info':
        default:
            alertClass = 'text-primary';
            title = 'Information';
    }

    // Mettre à jour le titre et le contenu
    modalTitle.textContent = title;
    modalBody.innerHTML = `<p class="${alertClass}">${message}</p>`;

    // Afficher la modal
    const alertModal = new bootstrap.Modal(modal);
    alertModal.show();
}





// Initialisation des sélecteurs multi-choix
function initializeSelectors() {
    const completedFilter = document.getElementById('completedFilter');
    const responsibleFilter = document.getElementById('responsibleFilter');
    
    if (completedFilter) {
        window.completedSelect = new TomSelect('#completedFilter', {
            plugins: ['remove_button'],
            placeholder: 'Sélectionner les statuts'
        });
    }
    
    if (responsibleFilter) {
        window.responsibleSelect = new TomSelect('#responsibleFilter', {
            plugins: ['remove_button'],
            placeholder: 'Sélectionner les responsables'
        });
    }
}



// Export des données
function exportData() {
    document.getElementById('exportBtn').addEventListener('click', function() {
        const table = document.getElementById('activitiesTable');
        const wb = XLSX.utils.table_to_book(table);
        XLSX.writeFile(wb, 'activites_mail_' + new Date().toISOString().split('T')[0] + '.xlsx');
    });
}

// Sélection de toutes les lignes
function selectAllRows() {
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('tbody .form-check-input');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
}

// Initialisation Bitrix24
function initializeBitrix24() {
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            let $b24 = await B24Js.initializeB24Frame();
            let activitiesLinks = document.querySelectorAll('.activityLink');
            activitiesLinks.forEach(link => {
                link.addEventListener('click', async function() {
                    try {
                        await $b24.slider.openPath(
                            $b24.slider.getUrl('/bitrix/components/bitrix/crm.activity.planner/slider.php?ajax_action=ACTIVITY_VIEW&activity_id=' + this.getAttribute('data-id') + '&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER'),
                            950
                        );
                     
                    } catch (error) {
                        console.error(error);
                    }
                });
            });

            console.log($b24);
        } catch (error) {
            console.error(error);
        }
    });
}



// Fonction pour exporter les activités sélectionnées
function exportSelected() {

    // Event listener pour le bouton d'export
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', ()=>{
            const selectedCheckboxes = document.querySelectorAll('#activitiesTable tbody tr input[type="checkbox"]:checked');
    
            if (!selectedCheckboxes.length) {
                showAlert('Aucune activité sélectionnée pour l\'exportation.', 'warning');
                return; // Empêche l'ouverture de la boîte de dialogue d'enregistrement
            }
        
            // Création du tableau de données pour l'export
            const data = Array.from(selectedCheckboxes).map(checkbox => {
                const row = checkbox.closest('tr');
                return {
                    'ID': row.querySelector('td[id^="activityID_"]').textContent,
                    'Subject': row.querySelector('td[id^="activitySubject_"]').textContent,
                    'Responsible': row.querySelector('td[id^="activityResponsible_"]').textContent,
                    'CreatedDate': row.querySelector('td[id^="activityCreatedDate_"]').textContent,
                    'UpdatedDate': row.querySelector('td[id^="activityUpdatedDate_"]').textContent
                };
            });
            
            // Export en Excel
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Activités");
            XLSX.writeFile(wb, "activites_export.xlsx");
        });
    }

    
}

// Fonction pour sélectionner toutes les activités filtrées
function selectAllFiltered() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('#activitiesTable tbody tr:not([style*="display: none"]) input[type="checkbox"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

// Fonction pour sélectionner toutes les activités filtrées (même celles des autres pages)
function selectAllFilteredActivities() {
    const selectAllFilteredCheckbox = document.getElementById('selectAllFiltered');
    const allCheckboxes = document.querySelectorAll('#activitiesTable tbody tr input[type="checkbox"]');
    
    allCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        // Si la ligne n'est pas cachée par les filtres
        if (row.style.display !== 'none') {
            checkbox.checked = selectAllFilteredCheckbox.checked;
        }
    });
}





