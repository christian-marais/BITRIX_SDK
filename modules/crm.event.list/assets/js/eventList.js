// Fonction de tri du tableau
function sortTable(columnIndex, sortType) {
    console.log('Début du tri - Colonne:', columnIndex, 'Type:', sortType);

    const table = document.getElementById('activitiesTable');
    if (!table) {
        console.error('Table not found');
        return;
    }

    const tbody = table.querySelector('tbody');
    if (!tbody) {
        console.error('Table body not found');
        return;
    }

    const rows = Array.from(tbody.querySelectorAll('tr'));
    const headers = table.querySelectorAll('thead th');

    console.log('Nombre de lignes:', rows.length);

    // Déterminer l'ordre de tri
    const currentHeader = headers[columnIndex];
    const currentSortOrder = currentHeader.getAttribute('data-sort-order') || 'asc';
    const newSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';

    console.log('Ordre de tri:', newSortOrder);

    // Réinitialiser tous les en-têtes
    headers.forEach(header => {
        const icon = header.querySelector('.bi');
        if (icon) {
            icon.classList.remove('bi-arrow-up', 'bi-arrow-down');
            icon.classList.add('bi-arrow-down-up');
            header.removeAttribute('data-sort-order');
        }
    });

    // Mettre à jour l'en-tête courant
    const sortIcon = currentHeader.querySelector('.bi');
    if (sortIcon) {
        sortIcon.classList.remove('bi-arrow-down-up');
        sortIcon.classList.add(newSortOrder === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down');
        currentHeader.setAttribute('data-sort-order', newSortOrder);
    }

    // Fonction de comparaison selon le type de tri
    const compareValues = (a, b) => {
        const cellA = a.querySelectorAll('td')[columnIndex];
        const cellB = b.querySelectorAll('td')[columnIndex];
        
        if (!cellA || !cellB) {
            console.error('Cellules non trouvées', cellA, cellB);
            return 0;
        }

        let valueA = cellA.textContent.trim();
        let valueB = cellB.textContent.trim();

        console.log('Valeurs à comparer:', valueA, valueB);

        switch(sortType) {
            case 'id':
                return parseInt(valueA || 0) - parseInt(valueB || 0);
            
            case 'created':
            case 'updated':
                const parseDate = (dateStr) => {
                    const [day, month, year] = dateStr.split(/[/ :]/);
                    return new Date(year, month - 1, day);
                };
                const dateA = parseDate(valueA);
                const dateB = parseDate(valueB);
                return dateA - dateB;
            
            default:
                return valueA.localeCompare(valueB);
        }
    };

    // Trier les lignes
    rows.sort((a, b) => {
        const comparison = compareValues(a, b);
        return newSortOrder === 'asc' ? comparison : -comparison;
    });

    // Réinsérer les lignes triées
    rows.forEach(row => tbody.appendChild(row));

    console.log('Tri terminé');
}

// Initialiser les événements de tri
function initializeSorting() {
    const headers = document.querySelectorAll('#activitiesTable thead .sortable');
    console.log('En-têtes triables trouvés:', headers.length);

    headers.forEach((header, index) => {
        header.addEventListener('click', () => {
            const sortType = header.getAttribute('data-sort');
            console.log('Clic sur en-tête:', index, 'Type:', sortType);
            sortTable(index, sortType);
        });
    });
}

// Gestion de la pagination
function managePagination() {
    const itemsPerPageSelect = document.getElementById('itemsPerPage');
    const paginationLinks = document.querySelectorAll('.pagination .page-link');

    // Gestion du changement du nombre d'articles par page
    if (itemsPerPageSelect) {
        itemsPerPageSelect.addEventListener('change', function() {
            const newItemsPerPage = this.value;
            window.location.href = `${window.location.pathname}?itemsPerPage=${newItemsPerPage}&page=1`;
        });
    }

    // Gestion de la navigation entre pages
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.closest('.page-item').classList.contains('disabled')) {
                const page = this.getAttribute('data-page');
                const currentItemsPerPage = itemsPerPageSelect.value;
                
                window.location.href = `${window.location.pathname}?itemsPerPage=${currentItemsPerPage}&page=${page}`;
            }
        });
    });
}

// Fonction pour réinitialiser les filtres
function resetFilters() {
    // Réinitialisation des sélections Tom Select
    const tomSelects = [
        window.completedSelect, 
        window.responsibleSelect, 
        window.locationSelect, 
        window.collaboratorsSelect
    ];

    tomSelects.forEach(select => {
        if (select) {
            select.setValue([]); // Vider sans détruire
        }
    });

    // Réinitialisation des autres filtres
    const startDateFilter = document.getElementById('startDateFilter');
    const endDateFilter = document.getElementById('endDateFilter');
    const selectAll = document.getElementById('selectAll');

    if (startDateFilter) startDateFilter.value = '';
    if (endDateFilter) endDateFilter.value = '';
    if (selectAll) selectAll.checked = false;

    // Réafficher toutes les lignes
    document.querySelectorAll('#activitiesTable tbody tr').forEach(row => {
        row.style.display = '';
    });

    // Appliquer les filtres après réinitialisation
    applyFilters();
}

// Initialisation des sélecteurs Tom Select
function initializeLocationSelect() {
    const locationSelect = document.getElementById('locationFilter');
    if (locationSelect) {
        window.locationSelect = new TomSelect('#locationFilter', {
            create: false,
            placeholder: 'Sélectionner un lieu',
            maxItems: null,
            onDelete: applyFilters,
            onChange: applyFilters
        });
    }
}

function initializeCollaboratorsSelect() {
    const collaboratorsSelect = document.getElementById('collaboratorsFilter');
    if (collaboratorsSelect) {
        window.collaboratorsSelect = new TomSelect('#collaboratorsFilter', {
            create: false,
            placeholder: 'Sélectionner des collaborateurs',
            maxItems: null,
            onDelete: applyFilters,
            onChange: applyFilters
        });
    }
}

// Récupération des paramètres d'URL
function getUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    return {
        completed: urlParams.get('completed') ? urlParams.get('completed').split(',') : [],
        startDate: urlParams.get('startDate') || '',
        endDate: urlParams.get('endDate') || '',
        responsible: urlParams.get('responsible') ? urlParams.get('responsible').split(',') : [],
        location: urlParams.get('location') ? urlParams.get('location').split(',') : [],
        collaborators: urlParams.get('collaborators') ? urlParams.get('collaborators').split(',') : [],
        description: urlParams.get('description') ? decodeURIComponent(urlParams.get('description')) : '',
        page: urlParams.get('page') || '1',
        itemsPerPage: urlParams.get('itemsPerPage') || '10'
    };
}

// Initialisation des filtres à partir de l'URL
function initializeFiltersFromUrl() {
    const params = getUrlParams();

    // Fonction utilitaire pour initialiser un sélecteur Tom Select
    function initializeTomSelect(selectInstance, values) {
        if (selectInstance) {
            try {
                if (selectInstance.setValue) {
                    selectInstance.clear();
                    values.forEach(value => {
                        selectInstance.addItem(value);
                    });
                }
            } catch (error) {
                console.error(`Erreur lors de la mise à jour du sélecteur: ${error}`);
            }
        }
    }

    // Attendre un court instant pour s'assurer que Tom Select est initialisé
    setTimeout(() => {
        // Initialisation des sélecteurs Tom Select
        initializeTomSelect(window.locationSelect, params.location);
        initializeTomSelect(window.collaboratorsSelect, params.collaborators);
        initializeTomSelect(window.completedSelect, params.completed);
        initializeTomSelect(window.responsibleSelect, params.responsible);
    }, 200);

    // Initialisation des sélecteurs natifs
    const selectorsToInitialize = [
        { id: 'completedFilter', values: params.completed },
        { id: 'responsibleFilter', values: params.responsible }
    ];

    selectorsToInitialize.forEach(({ id, values }) => {
        const select = document.getElementById(id);
        if (select) {
            Array.from(select.options).forEach(option => {
                option.selected = values.includes(option.value);
            });
        }
    });

    // Initialisation des champs de date
    const dateFieldsToInitialize = [
        { id: 'startDateFilter', value: params.startDate },
        { id: 'endDateFilter', value: params.endDate }
    ];

    dateFieldsToInitialize.forEach(({ id, value }) => {
        const field = document.getElementById(id);
        if (field) {
            field.value = value;
        }
    });

    // Initialisation du nombre d'éléments par page
    const itemsPerPageSelect = document.getElementById('itemsPerPage');
    if (itemsPerPageSelect) {
        itemsPerPageSelect.value = params.itemsPerPage;
    }
}

// Application des filtres
function applyFilters() {
    const applyFiltersButton = document.getElementById('applyFilters');
    if (!applyFiltersButton) return;

    applyFiltersButton.addEventListener('click', function() {
        // Récupération des valeurs de filtres
        const getFilterValues = (selectElement) => 
            Array.from(selectElement.selectedOptions).map(opt => opt.value);

        const completedFilter = getFilterValues(document.getElementById('completedFilter'));
        const responsibleFilter = getFilterValues(document.getElementById('responsibleFilter'));
        
        const startDate = document.getElementById('startDateFilter').value;
        const endDate = document.getElementById('endDateFilter').value;
        
        const locationFilter = window.locationSelect ? window.locationSelect.getValue() : [];
        const collaboratorsFilter = document.getElementById('collaboratorsModalFilter') 
            ? getFilterValues(document.getElementById('collaboratorsModalFilter')) 
            : [];
        const subjectFilter = document.getElementById('subjectFilter') ? [document.getElementById('subjectFilter').value] : [];

        // Construction des paramètres de l'URL
        const queryParams = new URLSearchParams();

        const addFilterToParams = (filterName, values) => {
            if (values && values.length > 0) {
                queryParams.append(filterName, values.join(','));
            }
        };

        addFilterToParams('completed', completedFilter);
        addFilterToParams('responsible', responsibleFilter);
        addFilterToParams('location', locationFilter);
        addFilterToParams('collaborators', collaboratorsFilter);
        addFilterToParams('subject', subjectFilter);
        
        if (startDate) queryParams.append('startDate', startDate);
        if (endDate) queryParams.append('endDate', endDate);

        // Pagination par défaut
        queryParams.append('page', 1);
        queryParams.append('itemsPerPage', document.getElementById('itemsPerPage').value);

        // Redirection
        window.location.href = `${window.location.pathname}?${queryParams.toString()}`;
    });
}

// Fonction de filtrage des collaborateurs
function filterCoworkers() {
    // Récupérer tous les badges de collaborateurs
    const coworkerBadges = document.querySelectorAll('.coworker-badge');
    const activeCoworkerBadges = document.querySelectorAll('.coworker-badge.bg-danger');
    const rows = document.querySelectorAll('#activitiesTable tbody tr');

    // Récupérer les collaborateurs depuis l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const collaboratorsParam = urlParams.get('collaborators');
    
    // Si aucun badge n'est sélectionné et pas de paramètre URL, afficher toutes les lignes
    if (activeCoworkerBadges.length === 0 && !collaboratorsParam) {
        rows.forEach(row => row.style.display = '');
        return;
    }

    // Filtrer les lignes
    rows.forEach(row => {
        const collaboratorsCell = row.querySelector('td[data-collaborators]');
        if (collaboratorsCell) {
            const rowCollaborators = collaboratorsCell.getAttribute('data-collaborators').split(',');
            
            // Vérifier si la ligne contient au moins un des collaborateurs sélectionnés
            const hasMatchingCollaborator = 
                // Vérifier les badges sélectionnés
                Array.from(activeCoworkerBadges).some(badge => 
                    rowCollaborators.includes(badge.getAttribute('data-coworker-id'))
                ) || 
                // Vérifier les collaborateurs de l'URL
                (collaboratorsParam && 
                    collaboratorsParam.split(',').some(collaboratorId => 
                        rowCollaborators.includes(collaboratorId)
                    )
                );
            
            row.style.display = hasMatchingCollaborator ? '' : 'none';
        }
    });
}

// Initialisation du filtre collaborateurs dans le modal
function  initializeModalCoworkerFilter() {
    const collaboratorsModalSelect = document.getElementById('collaboratorsModalFilter');
    
    if (collaboratorsModalSelect) {
        const tomSelect = new TomSelect('#collaboratorsModalFilter', {
            create: false,
            placeholder: 'Sélectionner des collaborateurs',
            maxItems: null
        });

        // Récupérer les collaborateurs depuis l'URL
        const urlParams = new URLSearchParams(window.location.search);
        const collaboratorsParam = urlParams.get('collaborators');
        
        if (collaboratorsParam) {
            const selectedCollaborators = collaboratorsParam.split(',');
            tomSelect.setValue(selectedCollaborators);
        }
    }
}

// Initialisation des événements de filtrage des collaborateurs
function initializeCoworkerFilters() {
    const coworkerBadges = document.querySelectorAll('.coworker-badge');
    
    // Récupérer les collaborateurs depuis l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const collaboratorsParam = urlParams.get('collaborators');

    coworkerBadges.forEach(badge => {
        const removeIcon = badge.querySelector('.remove-coworker-filter');
        const coworkerId = badge.getAttribute('data-coworker-id');

        // Vérifier si ce collaborateur est dans l'URL
        if (collaboratorsParam && collaboratorsParam.split(',').includes(coworkerId)) {
            badge.classList.remove('bg-secondary');
            badge.classList.add('bg-danger');
            removeIcon.classList.remove('d-none');
        }
        
        function updateCoworkerParameter() {
            const url = new URL(window.location.href);
            const selectedCoworkers = Array.from(coworkerBadges)
            .filter(badge => badge.classList.contains('bg-danger'))
            .map(badge => badge.getAttribute('data-coworker-id'))
            .join(',');
            if(selectedCoworkers) {
                url.searchParams.set('collaborators', selectedCoworkers);
            } else {
                url.searchParams.delete('collaborators');
            }
            window.history.replaceState({}, '', url.toString());
            window.location.reload();
        }
      
        // Événement de clic sur le badge
        badge.addEventListener('click', function() {
            this.classList.toggle('bg-danger');
            this.classList.toggle('bg-secondary');
            
            // Afficher/masquer la croix
            if (this.classList.contains('bg-danger')) {
                removeIcon.classList.remove('d-none');
            } else {
                removeIcon.classList.add('d-none');
            }
            updateCoworkerParameter();
            // initializeModalCoworkerFilter();
            // filterCoworkers();
            
        });
        
        // Événement de clic sur la croix pour supprimer le filtre
        removeIcon.addEventListener('click', function(e) {
            e.stopPropagation(); // Empêcher la propagation du clic au badge
            const badge = this.closest('.coworker-badge');
            badge.classList.remove('bg-danger');
            badge.classList.add('bg-secondary');
            this.classList.add('d-none');
            updateCoworkerParameter();
            // filterCoworkers();
            
        });
       
       
    });

    // Appliquer le filtrage initial
    filterCoworkers();
}
function initializeSubjectFilter() {
    subjectParam = new URLSearchParams(window.location.search).get('subject');
    document.getElementById('subjectFilter').value = subjectParam;
}

function initializeResetFilterBtn() {
    // Event listener pour le bouton de réinitialisation
    const resetBtn = document.getElementById('resetFiltersBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            window.location.href = window.location.pathname;
        });
    }
}
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
                    'UpdatedDate': row.querySelector('td[id^="activityUpdatedDate_"]').textContent,
                    'Collaborators': row.querySelector('td[id^="activityCollaborators_"]').textContent
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
// Initialisation globale
document.addEventListener('DOMContentLoaded', function() {
    initializeSelectors();
    initializeLocationSelect();

    initializeResetFilterBtn();
    initializeModalCoworkerFilter();
    initializeCoworkerFilters();
    initializeSubjectFilter();

    exportSelected();
    selectAllRows();
    
    initializeBitrix24();

    managePagination();
    initializeSorting();
    initializeFiltersFromUrl();
    applyFilters();

    // Event listeners pour les cases à cocher
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', selectAllFiltered);
    }

    const selectAllFilteredCheckbox = document.getElementById('selectAllFiltered');
    if (selectAllFilteredCheckbox) {
        selectAllFilteredCheckbox.addEventListener('change', selectAllFilteredActivities);
    }

    

    // Ajout de la classe bootstrap pour les cases à cocher
    const checkboxes = document.querySelectorAll('#activitiesTable tbody tr input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.classList.add('form-check-input');
    });
});