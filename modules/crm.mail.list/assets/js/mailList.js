// Gestion de la pagination
function managePagination() {
    document.addEventListener('DOMContentLoaded', function() {
        const itemsPerPageSelect = document.getElementById('itemsPerPage');
        const paginationLinks = document.querySelectorAll('.pagination .page-link');

        // Gestion du changement du nombre d'articles par page
        itemsPerPageSelect.addEventListener('change', function() {
            const newItemsPerPage = this.value;
            window.location.href = window.location.pathname + 
                '?itemsPerPage=' + newItemsPerPage + 
                '&page=1'; // Réinitialiser à la première page
        });

        // Gestion de la navigation entre pages
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if (!this.closest('.page-item').classList.contains('disabled')) {
                    const page = this.getAttribute('data-page');
                    const currentItemsPerPage = itemsPerPageSelect.value;
                    
                    window.location.href = window.location.pathname + 
                        '?itemsPerPage=' + currentItemsPerPage + 
                        '&page=' + page;
                }
            });
        });
    });
}

// Gestion des filtres
function applyFilters() {
    document.getElementById('applyFilters').addEventListener('click', function() {
        const completedFilter = Array.from(document.getElementById('completedFilter').selectedOptions).map(opt => opt.value);
        const startDate = document.getElementById('startDateFilter').value;
        const endDate = document.getElementById('endDateFilter').value;
        const responsibleFilter = Array.from(document.getElementById('responsibleFilter').selectedOptions).map(opt => opt.value);

        const rows = document.querySelectorAll('#activitiesTable tbody tr');
        rows.forEach(row => {
            const completed = row.querySelector('td:nth-child(7) .badge').textContent.includes('Terminé') ? 'Y' : 'N';
            const createdDate = row.querySelector('td:nth-child(4)').textContent;
            const responsible = row.querySelector('td:nth-child(6)').getAttribute('data-id');

            const completedMatch = completedFilter.length === 0 || completedFilter.includes(completed);
            const dateMatch = (!startDate || new Date(createdDate.split('/').reverse().join('-')) >= new Date(startDate)) &&
                              (!endDate || new Date(createdDate.split('/').reverse().join('-')) <= new Date(endDate));
            const responsibleMatch = responsibleFilter.length === 0 || responsibleFilter.includes(responsible);

            row.style.display = completedMatch && dateMatch && responsibleMatch ? '' : 'none';
        });

        document.getElementById('filterModal').querySelector('.btn-close').click();
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

// // Fonction pour réinitialiser les filtres
// function resetFilters() {
//     // Réinitialiser les champs de filtre
//     document.getElementById('filterField1').value = '';
//     document.getElementById('filterField2').value = '';
//     // Ajoutez d'autres champs de filtre si nécessaire

//     // Appliquer les filtres par défaut
//     applyFilters();
// }

// Fonction pour réinitialiser les filtres
function resetFilters() {
    // Réinitialisation des sélections Tom Select
    if (window.completedSelect) {
        window.completedSelect.setValue([]); // Vider sans détruire
    }
    if (window.responsibleSelect) {
        window.responsibleSelect.setValue([]); // Vider sans détruire
    }

    // Réinitialisation des autres filtres
    document.getElementById('startDateFilter').value = '';
    document.getElementById('endDateFilter').value = '';

    // Réafficher toutes les lignes
    document.querySelectorAll('#activitiesTable tbody tr').forEach(row => {
        row.style.display = '';
    });

    // Décocher la case "Tout sélectionner"
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.checked = false;
    }

    // Appliquer les filtres après réinitialisation
    applyFilters();
}


// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    initializeSelectors();
    applyFilters();
    exportSelected();
    selectAllRows();
    initializeBitrix24();
    managePagination();
    
    // Event listener pour la case à cocher "Tout sélectionner"
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', selectAllFiltered);
    }

    // Event listener pour la case à cocher "Tout sélectionner (filtré)"
    const selectAllFilteredCheckbox = document.getElementById('selectAllFiltered');
    if (selectAllFilteredCheckbox) {
        selectAllFilteredCheckbox.addEventListener('change', selectAllFilteredActivities);
    }

    // Event listener pour le bouton de réinitialisation
    const resetBtn = document.getElementById('resetFiltersBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', resetFilters);
    }

    // Ajout de la classe bootstrap pour les cases à cocher
    const checkboxes = document.querySelectorAll('#activitiesTable tbody tr input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.classList.add('form-check-input'); // Ajout de la classe bootstrap
    });
});
