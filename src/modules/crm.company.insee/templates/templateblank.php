<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <title>Annuaire d'Entreprises</title>
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
            z-index: 100;
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
        <?php global $request;if($back=$request->server->get('HTTP_REFERER')): ?>
        <div class="btn btn-info"><a class="text-decoration-none text-white" type="button" style="font-weight:bold;"href="<?=$back?>"><i class="bi bi-arrow-left"></i> Retour</a></div>
        <?php endif; ?>
        <a type="button" id="parameter" class="text-white btn btn-primary mx-2" href="<?=FULL_BASE_URL.'/webhook'?>">
            <i class="bi bi-gear " ></i> Paramètres
        </a>
        <hr>
        <div class="container">
            <h1 class="search-title text-center">Annuaire d'Entreprises</h1>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control search-input" 
                               placeholder="Rechercher par nom, SIRET, SIREN..." 
                               aria-label="Recherche" value="<?php !empty($company['TITLE'])?$company['TITLE']:'';?>">
                        <button class="btn btn-primary search-button" type="button" id="button-addon2">
                            <i class="bi bi-search me-2"></i>Rechercher
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php 
        $https=$request?->server->get("SERVER_PORT")=='443'?'https://':'http://';
        $domain =$https.$request?->server->get('HTTP_HOST');
        $baseUrl=$domain.$request?->server->get('SCRIPT_NAME');
    ?>
    <div class="container results-container">
        <div id="results" class="row">
        </div>
        </div>
    </div>
    <?php foreach($contents??[] as $content) : echo $content; endforeach; ?>
   
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/@bitrix24/b24jssdk@latest/dist/umd/index.min.js"></script>
    <script>
        <?php 
        ob_start();
        include dirname(__DIR__,2) . '/base/assets/js/slider.js';
        echo ob_get_clean();
        ?>
        let siretField="<?=$company["fields"]["bitrix"]["siret"]??''?>"
        let isAborted = false;
        let activeControllers = {}; // Store active controllers for each unique request
        let active='';
        function showCompany(sirets) {
            const requestKey = sirets.join(',');
            active=requestKey;
        
            // Abort the previous request if it exists
            Object.keys(activeControllers).forEach((key) => { 
                    activeControllers[key].abort();
                    console.log(`Aborting previous request for: ${key}`);
                    delete activeControllers[key];
            })
            // Create a new AbortController for the current request
            const abortController = new AbortController();
            activeControllers[requestKey] = abortController; // Store the controller
            const signal = abortController.signal;
        
            const batch = sirets.map(siret => ({
                method: 'crm.company.list',
                name: siret,
                params: {
                    ["filter[" + siretField + "]"]: siret
                }
            }));
        
            console.log('Payload:', JSON.stringify(batch));
        
            try {
                let go=true;
                setTimeout(async() => {
                    if(active!==requestKey){
                        go=false;
                        return;
                    }
                    const response = await fetch('<?=$baseUrl?>/api/companies/siret', {
                    signal,
                    method: 'POST',
                    body: JSON.stringify(batch)
                });
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('HTTP error', response.status, errorText);
                } else {
                    const data = await response.json();
                    console.log('datas',data.data)
                    if(data.status==="success"){
                        datas=data.data;
                        
                        if(typeof datas==="object"){
                            sirets.map(siret => {
                                const button=document.getElementById('showCompany'+siret);
                                button.innerText='Ajouter';
                                button.style.backgroundColor='#0D6EFD';
                                button.style.color='white';
                                button.setAttribute('onclick', "addCompanyToBitrix('"+siret+"')");
                            })
                            for (let [key,value] of Object.entries(datas)) {
                                if (datas.hasOwnProperty(key)) {
                                    if(datas[key]!=null){
                                        const url='<?=$domain?>/crm/company/details/'+value+'/';
                                        const localUri='<?=B24_DOMAIN?>/crm/company/details/'+value+'/';
                                        const querySelector='#showCompany'+key;
                                        const button=document.getElementById('showCompany'+key);
                                        button.innerText='Entreprise trouvée';
                                        button.style.backgroundColor='#0D6EFD';
                                        button.style.color='white';
                                        button.removeAttribute('onclick');
                                        <?php if(defined('IS_B24_IMPLEMENTED') && IS_B24_IMPLEMENTED): ?>
                                        setBitrix24Slider(querySelector,url,localUri);
                                        <?php else: ?>
                                        button.addEventListener('click', function() {
                                            window.location.href = localUri;
                                        })
                                        <?php endif; ?>
                                        button.innerText='Bitrix';
                                    }
                                }
                            }
                        }
                        const id=<?= $company["ID"]??null;?>'';
                        
                        if(id){
                            sirets.map(siret => {
                                const button=document.getElementById('updateCompany'+siret);
                                button.setAttribute('onclick', "updateCompanyToBitrix('"+siret+"','"+id+"')");
                            })
                        }
                    }
                    console.log('Response data:', data);
                }
        
                }, 2000);
                
                // Check if the request was aborted before processing the response
        
                
            } catch (error) {
                if (error.name === 'AbortError') {
                    console.log('Fetch request was aborted for:', requestKey);
                } else {
                    console.error('Fetch error:', error);
                }
            } 
        }
    
        
        // Fonction pour ajouter une entreprise dans Bitrix
        function addCompanyToBitrix(siret) {
            data= $.ajax({
                url: '<?=$baseUrl?>/api/company/'+siret+'/save',
                method: 'GET',
                success: function(data) {
                    if(data.status==='success'){
                        const btn = document.getElementById('showCompany'+siret);
                        const querySelector='#showCompany'+siret;
                        const localUri='<?=B24_DOMAIN?>/crm/company/details/'+data.result+'/';
                        const uri='<?=$domain?>/crm/company/details/'+data.result+'/';
                        btn.innerText='Entreprise ajoutée';
                        btn.style.backgroundColor='green';
                        btn.style.color='white';
                        btn.removeAttribute('onclick');
                        <?php if(defined('IS_B24_IMPLEMENTED') && IS_B24_IMPLEMENTED): ?>
                        setBitrix24Slider(querySelector,uri,localUri);
                        <?php else: ?>
                        btn.addEventListener('click', function() {
                            window.location.href = localUri;
                        })
                        <?php endif; ?>
                        btn.innerText='Bitrix';
                    }
                }
            });
        }

        function updateCompanyToBitrix(siret,id) {
            data= $.ajax({
                url: '<?=$baseUrl?>/api/company/'+id+'/'+siret+'/update',
                method: 'GET',
                success: function(data) {
                    console.log("mydatas",data)
                    if(data.status==='success'){
                        const btn = document.getElementById('updateCompany'+siret);
                        const querySelector='#updateCompany'+siret;
                        const localUri='<?=B24_DOMAIN?>/crm/company/details/'+data.result.id+'/';
                        const uri='<?=$domain?>/crm/company/details/'+data.result.id+'/';
                        btn.innerText='Entreprise modifiée';
                        btn.style.backgroundColor='green';
                        btn.style.color='white';
                        btn.removeAttribute('onclick');
                        <?php if(defined('IS_B24_IMPLEMENTED') && IS_B24_IMPLEMENTED): ?>
                        setBitrix24Slider(querySelector,uri,localUri);
                        <?php else: ?>
                        btn.addEventListener('click', function() {
                            window.location.href = localUri;
                        })
                        <?php endif; ?>
                        btn.innerText='Continuer';
                    }
                }
            });
        }

    </script>
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
                                        <button id="showCompanyInsee${etablissement.siret}" type="button" class="btn btn-primary" data-siret="${etablissement.siret}" data-siren="${siren}">
                                            INSEE
                                        </button>
                                        <button id="showCompany${etablissement.siret}" type="button" class="btn btn-primary addCompany add-to-bitrix" data-siret="${etablissement.siret}" data-siren="${siren}">
                                            <i class="bi bi-plus-circle me-2"></i>Recherche...
                                        </button>
                                    <?php if(defined('IS_B24_IMPLEMENTED') && IS_B24_IMPLEMENTED && !empty($company["ID"])): ?>
                                        <button id="updateCompany${etablissement.siret}" type="button" class="btn btn-primary updateCompany" data-siret="${etablissement.siret}" data-siren="${siren}">
                                            <i class="bi bi-pencil me-2"></i>Enrichir
                                        </button>
                                    <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('\n');
                
            }

            function showInsee(sirets){
                sirets.forEach(siret => {
                    <?php if(defined('IS_B24_IMPLEMENTED') && IS_B24_IMPLEMENTED): ?>
                        setBitrix24Slider("#showCompanyInsee"+siret,"<?=FULL_BASE_URL?>/company/"+siret);
                    <?php else: ?>
                        if(button=document.getElementById("showCompanyInsee"+siret))
                        {
                        button.addEventListener('click', ()=>window.location.href = "<?=FULL_BASE_URL?>/company/"+siret);
                        }
                    <?php endif; ?>
                })
            }

            searchInput.addEventListener('input', function() {
                const keyword = this.value;
                if (keyword.length > 2) {
                    fetch(`https://recherche-entreprises.api.gouv.fr/search?q=${keyword}&page=1&per_page=5`)
                        .then(response => response.json())
                        .then(data => {
                            resultsDiv.innerHTML = '';
                            const currentSirets = new Set();
                            data.results.forEach(company => {
                                company.matching_etablissements.forEach(etablissement => {
                                    currentSirets.add(etablissement.siret);
                                });
                            });

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
                            const currentRequestKey = Array.from(currentSirets).join(',');
                            let sirets=[]
                            btns=document.querySelectorAll('.addCompany')
                            btns.forEach((element) => {
                                sirets.push(element.getAttribute('data-siret'))
                            });
                            showCompany(sirets);
                            showInsee(sirets);
                        });
                    }
                });
            });
    </script>
</body>
</html>
