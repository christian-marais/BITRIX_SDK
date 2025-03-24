<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <title>Annuaire d'Enterprises</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .search-container {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .search-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .search-input {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .search-button {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
        }
        .results-container {
            padding: 2rem 0;
        }
        .company-card {
            background: hsl(0, 0%, 89%);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .company-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            padding: 1.5rem;
        }
        .company-title {
            color: #2c3e50;
            font-size: 1.6rem;
            font-style: normal;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .company-badge {
            background-color: #e9ecef;
            color: #495057;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            margin-right: 0.5rem;
        }
        .company-content {
            padding: 1.5rem;
        }
        .establishment-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .establishment-item {
            background-color: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 1rem;
            padding: 1rem;
        }
        .establishment-title {
            color: #2c3e50;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .establishment-info {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        .btn-view {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        .btn-view:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }
       
    </style>
</head>
<body>
    <div class="search-container mb-4">
        <div class="container">
            <h1 class="search-title text-center">Annuaire d'Enterprises</h1>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control search-input" 
                               placeholder="Rechercher par nom, SIRET, SIREN..." 
                               aria-label="Recherche">
                        <button class="btn btn-primary search-button" type="button" id="button-addon2">
                            <i class="bi bi-search me-2"></i>Rechercher
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $domain ='http://'.$request->server->get('HTTP_HOST').'/' ;?>
    <div class="container results-container">
        <div id="results" class="row">
        </div>
    </div>
    <?php foreach($contents??[] as $content) : echo $content; endforeach; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const resultsDiv = document.getElementById('results');

            function renderEstablishments(etablissements, siren) {
                return etablissements.map((etablissement, i) => {
                    const statusBadge =  '<span class="company-badge text-white ' + (etablissement.etat_administratif === 'A' ? ' bg-success ">En activité</span>': 'bg-danger">Fermé</span>');
                    const siegeBadge = etablissement.est_siege ? '<span class="company-badge text-white bg-warning">Siège</span>': '';
                    return `
                        <div class="establishment-item">
                            <div class="row align-items-center">
                                <div class="col">
                                
                                    <h4 class="establishment-title">Établissement ${i + 1} ${siegeBadge} ${statusBadge}</h4>
                                    <p class="establishment-info">
                                        <i class="bi bi-upc me-2"></i>SIRET: ${etablissement.siret}
                                    </p>
                                    <p class="establishment-info">
                                        <i class="bi bi-geo-alt me-2"></i>${etablissement.adresse}
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary addCompany add-to-bitrix" data-siret="${etablissement.siret}" data-siren="${siren}">
                                            <i class="bi bi-plus-circle me-2"></i>Ajouter
                                        </button>
                                        <a href="#" class="btn btn-secondary ms-2 view-in-bitrix" style="display: none;" data-siret="${etablissement.siret}">
                                            <i class="bi bi-eye me-2"></i>Voir
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('\n');
            }

            searchInput.addEventListener('input', function() {
                const keyword = this.value;
                if (keyword.length > 2) {
                    fetch(`https://recherche-entreprises.api.gouv.fr/search?q=${keyword}&page=1&per_page=5`)
                        .then(response => response.json())
                        .then(data => {
                            resultsDiv.innerHTML = '';
                            data.results.forEach(company => {
                                const card = `
                                <div class="col-md-6 mb-4">
                                    <div class="company-card card">
                                        <div class="card-header company-header">
                                            <h3 class="company-title">${company.nom_complet}</h3>
                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="company-badge">
                                                    <i class="bi bi-building me-2"></i>SIREN: ${company.siren}
                                                </span>
                                                <span class="company-badge">
                                                    <i class="bi bi-tag me-2"></i>NAF: ${company.siege.activite_principale}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="company-content card-body">
                                            <div class="establishment-list">
                                                ${renderEstablishments(company.matching_etablissements, company.siren)}
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                                resultsDiv.innerHTML += card;
                            });
                        });
                    }
                });
            });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/@bitrix24/b24jssdk@latest/dist/umd/index.min.js"></script>
    <script src="../../../base/assets/js/slider.js"></script>
    <script>
    $(document).ready(function() {
        document.querySelectorAll('.addCompany').forEach(element => {
            element.addEventListener('click', function() {
                showCompany(this.getAttribute('data-siret'));
            });
        });
        function addCompany(siret){
            fetch('/api/company/'+siret+'/save')
            .then(response => response.json())
            .then(
                data => {
                    if(data.status==="success"){
                        window.location.reload();
                  }
                }
            );
        }

        function showCompany(siret){
            fetch('/api/company/'+siret+'/')
            .then(response => response.json())
            .then(
                data => {
                    if(data.status==="success"){
                        const id='showCompany'+siret;
                        const button=document.getElementById(id);
                        const  url='<?=$domain?>crm/company/details/'+data.result+'/';
                        button.setAttribute('onclick', "setBitrix24Slider('"+id+"','"+url+"')");
                        button.innerText='Consulter l\'entreprise sur bitrix';
                  }
                }
            );
        }
        
        setBitrix24Slider();
        function showCompany(companyId){
            fetch('<?=$domain?>crm/company/details/'+companyId+'/')
            .then(response => response.json())
            .then(
                data => {
                    if(data.status==="success"){
                        const button=document.getElementById('showCompany'+siret);
                        button.setAttribute('onclick', "showCompany('"+siret+"')");
                        button.innerText='Consulter l\'entreprise sur bitrix';
                  }
                }
            );
        }
        // Fonction pour vérifier si une entreprise existe dans Bitrix
        function checkCompanyInBitrix(siret) {
            return $.ajax({
                url: '/api/company/'+siret,
                method: 'GET'
            });
        }

        // Fonction pour sauvegarder une entreprise en base de données
        function saveCompanyToDb(data) {
            return $.ajax({
                url: '/api/company/save-to-db',
                method: 'POST',
                data: data
            });
        }

        // Fonction pour ajouter une entreprise dans Bitrix
        function addCompanyToBitrix(siret) {
            return $.ajax({
                url: '/api/company/'+siret+'/save',
                method: 'GET'
            });
        }

        // Gestion du clic sur le bouton "Ajouter dans Bitrix"
        $(document).on('click', '.add-to-bitrix', function() {
            const $btn = $(this);
            const siret = $btn.data('siret');
            const siren = $btn.data('siren');
            const $viewBtn = $btn.siblings('.view-in-bitrix');

            checkCompanyInBitrix(siret).then(function(response) {
                if (response.exists) {
                    $btn.hide();
                    $viewBtn.show().attr('href', response.url);
                } else {
                    // Vérifier si c'est un établissement secondaire
                    checkCompanyInBitrix(siren).then(function(parentResponse) {
                        const companyData = {
                            siret: siret,
                            siren: siren,
                            parentId: parentResponse.exists ? parentResponse.id : null
                        };

                        addCompanyToBitrix(companyData).then(function(addResponse) {
                            alert('Entreprise ajoutée avec succès !');
                            $btn.hide();
                            $viewBtn.show().attr('href', addResponse.url);
                        }).catch(function() {
                            alert('Erreur lors de l\'ajout de l\'entreprise');
                        });
                    });
                }
            });
        });

        // Gestion du clic sur le bouton "Sauvegarder"
        $(document).on('click', '.save-to-db', function() {
            const $btn = $(this);
            const siret = $btn.data('siret');
            const siren = $btn.data('siren');

            const companyData = {
                siret: siret,
                siren: siren
            };

            saveCompanyToDb(companyData).then(function() {
                alert('Entreprise sauvegardée avec succès !');
            }).catch(function() {
                alert('Erreur lors de la sauvegarde de l\'entreprise');
            });
        });
    });
</script>
</body>
</html>
