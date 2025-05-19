<?php
use NS2B\SDK\MODULES\BASE\WebhookManager;
$webhookManager = new WebhookManager();
$webhook = $webhookManager->getWebhook();

if (!$webhook) {
    header('Location: /404.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recherche d'entreprises</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <div id="searchResults"></div>
    </div>

    <template id="companyTemplate">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title company-name"></h5>
                <p class="card-text company-address"></p>
                <div class="btn-group">
                    <button class="btn btn-primary add-to-bitrix">Ajouter dans Bitrix</button>
                    <button class="btn btn-success save-to-db">Sauvegarder en base</button>
                    <a href="#" class="btn btn-info view-in-bitrix" style="display: none;">Consulter dans Bitrix</a>
                </div>
            </div>
        </div>
    </template>

    <script>
    $(document).ready(function() {
        function displayCompanies(companies) {
            const $results = $('#searchResults');
            $results.empty();
            
            companies.forEach(company => {
                const $template = $('#companyTemplate').clone().html();
                const $company = $(template);
                
                $company.find('.company-name').text(company.name);
                $company.find('.company-address').text(company.address);
                
                // Configuration des boutons
                const $addButton = $company.find('.add-to-bitrix');
                const $viewButton = $company.find('.view-in-bitrix');
                const $saveButton = $company.find('.save-to-db');
                
                $addButton.data('company', company);
                
                // Vérification si l'entreprise existe déjà dans Bitrix
                $.ajax({
                    url: '/api/company/check',
                    method: 'POST',
                    data: { siren: company.siren },
                    success: function(response) {
                        if (response.exists) {
                            $addButton.hide();
                            $viewButton.show()
                                .attr('href', response.url);
                        }
                    }
                });
                
                // Gestion du clic sur "Ajouter dans Bitrix"
                $addButton.click(function() {
                    const companyData = $(this).data('company');
                    $.ajax({
                        url: '/api/company/add',
                        method: 'POST',
                        data: companyData,
                        success: function(response) {
                            alert('Entreprise ajoutée avec succès !');
                            $addButton.hide();
                            $viewButton.show()
                                .attr('href', response.url);
                        },
                        error: function() {
                            alert('Erreur lors de l\'ajout de l\'entreprise');
                        }
                    });
                });
                
                // Gestion du clic sur "Sauvegarder en base"
                $saveButton.click(function() {
                    const companyData = $(this).closest('.card').find('.add-to-bitrix').data('company');
                    $.ajax({
                        url: '/api/company/save-to-db',
                        method: 'POST',
                        data: companyData,
                        success: function(response) {
                            alert('Entreprise sauvegardée en base avec succès !');
                        },
                        error: function() {
                            alert('Erreur lors de la sauvegarde en base');
                        }
                    });
                });
                
                $results.append($company);
            });
        }
        
        // Écoute des messages de la fenêtre parente
        window.addEventListener('message', function(event) {
            if (event.data.companies) {
                displayCompanies(event.data.companies);
            }
        });
    });
    </script>
</body>
</html>
