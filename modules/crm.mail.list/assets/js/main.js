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



// Appliquer la fonction sur la description dans le modal
function updateModalDescriptions() {
    document.querySelectorAll('.description-section').forEach(section => {
        const rawHtml = section.innerHTML;
        const cleanText = cleanMailBody(rawHtml);
        section.textContent = cleanText;
    });
}

// Initialisation des sélecteurs multi-choix
function initializeSelectors() {
    new TomSelect('#statusFilter', {
        plugins: ['remove_button'],
        placeholder: 'Sélectionner les statuts'
    });
    new TomSelect('#responsibleFilter', {
        plugins: ['remove_button'],
        placeholder: 'Sélectionner les responsables'
    });
}

// Gestion des filtres
function applyFilters() {
    document.getElementById('applyFilters').addEventListener('click', function() {
        const statusFilter = Array.from(document.getElementById('statusFilter').selectedOptions).map(opt => opt.value);
        const startDate = document.getElementById('startDateFilter').value;
        const endDate = document.getElementById('endDateFilter').value;
        const responsibleFilter = Array.from(document.getElementById('responsibleFilter').selectedOptions).map(opt => opt.value);

        const rows = document.querySelectorAll('#activitiesTable tbody tr');
        rows.forEach(row => {
            const status = row.querySelector('td:nth-child(7) .badge').textContent.includes('Terminé') ? 'Y' : 'N';
            const createdDate = row.querySelector('td:nth-child(4)').textContent;
            const responsible = row.querySelector('td:nth-child(6)').getAttribute('data-id');

            const statusMatch = statusFilter.length === 0 || statusFilter.includes(status);
            const dateMatch = (!startDate || new Date(createdDate.split('/').reverse().join('-')) >= new Date(startDate)) &&
                              (!endDate || new Date(createdDate.split('/').reverse().join('-')) <= new Date(endDate));
            const responsibleMatch = responsibleFilter.length === 0 || responsibleFilter.includes(responsible);

            row.style.display = statusMatch && dateMatch && responsibleMatch ? '' : 'none';
        });

        document.getElementById('filterModal').querySelector('.btn-close').click();
    });
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



// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    initializeSelectors();
    applyFilters();
    exportData();
    selectAllRows();
    initializeBitrix24();
    managePagination();
});
