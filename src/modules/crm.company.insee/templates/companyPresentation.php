<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php if (defined('FULL_BASE_URL')): ?>
    <link rel="stylesheet" href="<?=dirname(FULL_BASE_URL) ?>/assets/css/nextcloudForm.css">
    <?php endif; ?>
    <title>Établissement <?php echo htmlspecialchars($company['legalName']); ?></title>
    <style>
        body {
            font-family: 'Marianne', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #f6f6f6;
        }
        .badge-status {
            background-color: #1AB37C;
            color: white;
            padding: 0.5em 1em;
            border-radius: 20px;
        }
        .info-block {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        .info-header {
            border-bottom: 1px solid #e5e5e5;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }
        .info-content {
            padding: 1.5rem;
        }
        .info-row {
            display: flex;
            margin-bottom: 1rem;
        }
        .info-label {
            width: 200px;
            color: #666;
            font-weight: 500;
        }
        .info-value {
            flex: 1;
            color: #1e1e1e;
        }
        .establishment-card {
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            margin-bottom: 1rem;
            background-color: white;
        }
        .nav-tabs .nav-link {
            color: #666;
            border: none;
            padding: 1rem 1.5rem;
        }
        .nav-tabs .nav-link.active {
            color: #000;
            border-bottom: 2px solid #000;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php 
        $https=$request?->server->get("SERVER_PORT")=='443'?'https://':'http://';
        $domain =$https.$request?->server->get('HTTP_HOST');
        $baseUrl=$domain.$request?->server->get('SCRIPT_NAME');
        $companyId=$company["requisite"]["ENTITY_ID"]??"";
    ?>
    <?php include dirname(__FILE__,2) . '/snippets/buttonsBar.php'; ?>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3"><?php echo htmlspecialchars($company['legalName']).' '. $company["annuaire"]->sigle; ?></h1>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge-status">
                        <?php echo $company["annuaire"]->siege->etat_administratif === 'A' ? 'En activité' : 'Fermé'; ?>
                    </span>
                    <span class="text-muted">SIREN <?php echo htmlspecialchars($company["annuaire"]->siren); ?></span>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="#"><i class="bi bi-building me-2"></i>Fiche établissement</a>
            </li>
        </ul>
        <!-- Siège social -->
        <div class="info-block">
            <div class="info-header">
                <h2 class="h5 mb-0"><i class="bi bi-building me-2"></i>Siège social</h2>
            </div>
            <div class="info-content">
                <div class="info-row">
                    <div class="info-label">SIRET</div>
                    <div class="info-value"><?php echo htmlspecialchars($company["annuaire"]->siege->siret); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">SIREN</div>
                    <div class="info-value"><?php echo htmlspecialchars($company["annuaire"]->siren); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Adresse</div>
                    <div class="info-value"><?php echo htmlspecialchars($company["annuaire"]->siege->adresse); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Activité principale</div>
                    <div class="info-value"><?php echo htmlspecialchars($company["annuaire"]->siege->activite_principale); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Date de création</div>
                    <div class="info-value"><?php echo htmlspecialchars($company["annuaire"]->siege->date_creation); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Effectif</div>
                    <div class="info-value"><?php echo htmlspecialchars($company["annuaire"]->siege->tranche_effectif_salarie); ?> salariés</div>
                </div>
                <?php if ($company["annuaire"]->siege->date_fermeture): ?>
                    <div class="info-row">
                        <div class="info-label">Date de fermeture</div>
                        <div class="info-value"><?php echo htmlspecialchars($company["annuaire"]->siege->date_fermeture); ?></div>
                    </div>
                <?php endif; ?>
                <div class="info-row">
                        <div class="info-label">Date de mise à jour</div>
                        <div class="info-value"><?php echo htmlspecialchars($company["annuaire"]->siege->date_mise_a_jour_insee); ?></div>
                    </div>
            </div>
        </div>

        <!-- Établissements -->
        <div class="info-block">
            <div class="info-header">
                <h2 class="h5 mb-0">
                    <i class="bi bi-buildings me-2"></i>
                    Établissements (<?php echo count($company["annuaire"]->matching_etablissements).'/'. $company["annuaire"]->nombre_etablissements; ?>)
                </h2>
            </div>
            <div class="info-content">
                <?php foreach ($company["annuaire"]->matching_etablissements as $index => $etablissement): ?>
                    <div class="establishment-card p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h3 class="h6">
                                <?php echo $etablissement->est_siege ? 'Siège social' : 'Établissement secondaire'; ?>
                                <?php if ($etablissement->ancien_siege??false): ?>
                                    <span class="badge bg-warning">Ancien siège</span>
                                <?php endif; ?>
                                <span class="info-label mx-3"><?=$etablissement->siret?></span>
                            </h3>
                            <span class="badge <?php echo $etablissement->etat_administratif === 'A' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $etablissement->etat_administratif === 'A' ? 'En activité' : 'Fermé le '. htmlspecialchars($etablissement->date_fermeture); ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Nom</div>
                            <div class="info-value"><?php echo htmlspecialchars($etablissement->nom_complet); ?></div>
                            <div class="info-label">
                                <button id="showCompany<?=$etablissement->siret?>" data-siret="<?=$etablissement->siret?>" class="btn btn-primary addCompany" data-bs-toggle="modal" data-bs-target="#addCompanyModal">Recherche ...</button>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Adresse</div>
                            <div class="info-value"><?php echo htmlspecialchars($etablissement->adresse??$etablissement->geo_adresse); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">NAF</div>
                            <div class="info-value"><?php echo htmlspecialchars($etablissement->activite_principale); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date de création</div>
                            <div class="info-value"><?php echo htmlspecialchars($etablissement->date_creation); ?></div>
                        </div>
                    </div>
                   
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Dirigeants -->
        <div class="info-block">
            <div class="info-header">
                <h2 class="h5 mb-0"><i class="bi bi-people me-2"></i>Dirigeants</h2>
            </div>
            <div class="info-content">
                <?php $i=0;foreach ($company["annuaire"]->dirigeants as $dirigeant): ?>
                    <?php $i++; ?>
                    <?php if($dirigeant->type_dirigeant=='personne morale'):?>
                    <div class="info-row">
                    <div class="info-label"><?php echo htmlspecialchars($dirigeant->qualite); ?></div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($dirigeant->denomination).'<i class="bi bi-buildings mx-1"></i>'; ?>
                            <br>
                            <small class="text-muted">Siren : <?php echo htmlspecialchars($dirigeant->siren); ?></small>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="window.location.href=''">Ajouter l'entreprise</button>
                    </div>
                    <?php else: ?>
                    <div class="info-row">
                        <div class="info-label"><?php echo htmlspecialchars($dirigeant->qualite); ?></div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($dirigeant->prenoms . ' ' . $dirigeant->nom).' <i class="bi bi-person mx-1"></i>'; ?>
                            <br>
                            <small class="text-muted">Né(e) en <?php echo htmlspecialchars($dirigeant->annee_de_naissance); ?></small>
                        </div>
                        <?php if(!empty($companyId)): ?>
                            <div class="info-label">
                                <button class="btn btn-primary contact<?=$companyId ?>" id="contact<?=$i?>" data-nom="<?php echo htmlspecialchars($dirigeant->nom); ?>" data-qualite="<?php echo htmlspecialchars($dirigeant->qualite); ?>" data-prenom="<?php echo htmlspecialchars($dirigeant->prenoms); ?>" onclick="addContact('<?=$companyId?>',<?=$i?>)">Ajouter le contact</button>
                            </div>
                        <?php endif;?>
                    </div>
                    <?php endif;?>
                  
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Informations financières -->
        <?php if (!empty($company["annuaire"]->finances)): ?>
        <div class="info-block">
            <div class="info-header">
                <h2 class="h5 mb-0"><i class="bi bi-graph-up me-2"></i>Informations financières</h2>
            </div>
            <div class="info-content">
                <?php foreach ($company["annuaire"]->finances as $annee => $info): ?>
                    <div class="info-row">
                        <div class="info-label">Année <?php echo htmlspecialchars($annee); ?></div>
                        <div class="info-value">
                            <div>Chiffre d'affaires: <?php echo number_format($info->ca, 0, ',', ' '); ?> €</div>
                            <div>Résultat net: <?php echo number_format($info->resultat_net, 0, ',', ' '); ?> €</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        
        <!-- BODACC -->
        <?php if (!empty($records=$company["bodaccRecords"])): ?>
        <div class="info-block">
            <div class="info-header">
                <h2 class="h5 mb-0"><i class="bi bi-graph-up me-2"></i>Procédures collectives</h2>
            </div>
            <div class="info-content">
                <?php foreach ($records as $record): ?>
                    <div class="info-row">
                        <div class="info-label"><p>Parue le :  <?php echo $record["dateparution"]; ?></p></div>
                        <div class="info-value">
                            <p><span class="fw-bold badge bg-dark">Registre: </span>   <?php echo $record["registre"]. 'RCS '.$record["tribunal"]?></p>
                            <p><span class="fw-bold badge bg-dark">Description:</span>   <?php echo $record["description"]; ?></p>
                            <p><span class="fw-bold badge bg-danger">Jugement: </span>   <?php echo $record["jugement"]; ?></p>
                            <p><span class="fw-bold badge bg-dark"> Date du jugement :</span>   <?php echo $record["datejugement"]; ?></p>
                        </div>
                        <div class="info-label">     
                            <a class="btn btn-primary" title="consulter l'annonce" href="<?php echo $record['url_complete'];?>">Consulter l'annonce</a>
                        </div>
                    </div>
                    <hr>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/@bitrix24/b24jssdk@latest/dist/umd/index.min.js"></script>
    <script src="<?= dirname(FULL_BASE_URL) ?>/assets/js/nextcloudForm.js"></script>
    <script>
          <?php 
          
        ob_start();
        include dirname(__DIR__,2) . '/base/assets/js/slider.js';
        // include dirname(__DIR__,2) . '/base/assets/js/userDialogBox.js';
        echo ob_get_clean();
        ?>
        
        function addCompanyToBitrix(siret) {
            data= $.ajax({
                url: '<?=$baseUrl?>/api/company/'+siret+'/save',
                method: 'GET',
                success: function(data) {
                    if(data.status==='success'){
                        uri='<?=$domain?>/crm/company/details/'+data.result+'/';
                        querySelector='#showCompany'+siret;
                        const localUri='<?=B24_DOMAIN?>/crm/company/details/'+data.result+'/';
                        const btn = document.getElementById('showCompany'+siret);
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
                    }
                }
            });
        }

        function getContacts(companyId) {
            if(companyId===undefined){
                return;
            }
            const querySelectors='.contact'+companyId;
            let btns=document.querySelectorAll(querySelectors);
            btns.forEach(async function(btn){
                btn.innerText='Recherche...';
                let querySelector='#'+btn.getAttribute('id');
                const name=btn.getAttribute('data-nom');
                const qualite=btn.getAttribute('data-qualite');
                const first_name=btn.getAttribute('data-prenom');
                const response =await fetch('<?=$baseUrl?>/api/company/contacts',{
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        LAST_NAME: name,
                        NAME: first_name,
                        COMPANY_ID: companyId
                    })
                });
                data= await response.json();
                if(data.status==='success'){
                    uri='<?=$domain?>/crm/contact/details/'+data.result[0].ID+'/';
                    let localUri='<?=B24_DOMAIN?>/crm/contact/details/'+data.result[0].ID+'/';
                    btn.setAttribute('data-contact-id',data.result[0].ID);
                    btn.innerText='Voir';
                    btn.style.color='white';
                    btn.removeAttribute('onclick');
                    <?php if(defined('IS_B24_IMPLEMENTED') && IS_B24_IMPLEMENTED): ?>
                    setBitrix24Slider(querySelector,uri,localUri);
                    <?php else: ?>
                    btn.addEventListener('click', function() {
                        window.location.href = localUri;
                    })
                    <?php endif; ?>
                }else{
                    btn.innerText='Ajouter le contact';
                }
            })
        }

        async function addContact(companyId,id) {
            if(companyId===undefined){
                return;
            }
            const querySelector='#contact'+id;
            let btn=document.querySelector(querySelector);
            const name=btn.getAttribute('data-nom');
            const qualite=btn.getAttribute('data-qualite');
            const first_name=btn.getAttribute('data-prenom');
            const response =await fetch('<?=$baseUrl?>/api/company/contact/save',{
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    LAST_NAME: name,
                    NAME: first_name,
                    POST: qualite,
                    COMPANY_ID: companyId
                })
            });
            data= await response.json();
            if(data.status==='success'){
                
                uri='<?=$domain?>/crm/contact/details/'+data.result[0]+'/';
                const localUri='<?=B24_DOMAIN?>/crm/contact/details/'+data.result[0]+'/';
                btn.innerText='Contact ajouté';
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
            }
        }
    </script>
    <script>
        const b24Domain='<?=B24_DOMAIN?>';
        let siretField="<?=$company["fields"]["bitrix"]["siret"]??''?>"
        let isAborted = false;
        let activeControllers = {}; // Store active controllers for each unique request
        let active='';

        document.addEventListener('DOMContentLoaded', function() {
            let sirets=[]
            btns=document.querySelectorAll('.addCompany')
            btns.forEach((element) => {
                sirets.push(element.getAttribute('data-siret'))
            });
            getContacts(<?=$companyId?>);
            showCompany(sirets);
        });

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
                const go=true;
                setTimeout(async() => {
                    if(!active==requestKey){
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
                                button.innerText='Ajouter la société';
                                button.style.backgroundColor='#0D6EFD';
                                button.style.color='white';
                                button.setAttribute('onclick', "addCompanyToBitrix('"+siret+"')");
                            })
                            for (let [key,value] of Object.entries(datas)) {
                                if (datas.hasOwnProperty(key)) {
                                    if(datas[key]!=null){
                                        url='<?=$domain?>/crm/company/details/'+value+'/';
                                        localUrl=b24Domain+'/crm/company/details/'+value+'/';
                                        console.log('domain',b24Domain);
                                        querySelector='#showCompany'+key;
                                        const button=document.getElementById('showCompany'+key);
                                        button.innerText='Société trouvée';
                                        button.style.backgroundColor='#0D6EFD';
                                        button.style.color='white';
                                        button.removeAttribute('onclick');
                                        <?php if(defined('IS_B24_IMPLEMENTED') && IS_B24_IMPLEMENTED): ?>
                                        setBitrix24Slider(querySelector,url,localUrl);
                                        <?php else: ?>
                                        button.addEventListener('click', function() {
                                            window.location.href = localUrl;
                                        })
                                        <?php endif; ?>
                                        button.innerText='Voir';
                                    }
                                }
                            }
                        }else{
                            sirets.map(siret => {
                                const button=document.getElementById('showCompany'+siret);
                                button.innerText='Ajouter la société';
                                button.style.backgroundColor='#0D6EFD';
                                button.style.color='white';
                                button.setAttribute('onclick', "addCompanyToBitrix('"+siret+"')");
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
    </script>
</body>
</html>