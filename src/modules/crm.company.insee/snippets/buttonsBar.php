<?php global $request;if($back=$request->server->get('HTTP_REFERER')): ?>
<div class="btn btn-info"><a class="text-decoration-none text-white" type="button" style="font-weight:bold;"href="<?=$back?>"><i class="bi bi-arrow-left"></i> Retour</a></div>
<?php endif; ?>
<a type="button" id="search" class="text-white btn btn-primary mx-2" href="<?=FULL_BASE_URL?>/company/">
    <i class="bi bi-search me-2" ></i> Société
</a>
<a type="button" id="parameter" class="text-white btn btn-primary mx-2" href="<?=FULL_BASE_URL.'/webhook'?>">
    <i class="bi bi-gear " ></i> PARAMETRES
</a>
<hr>
<div class="card">
    <?php if(true||!empty($company['SIREN']) && $display=true): ?>
        <div class="card-body">
        
            <div class="card-title d-flex justify-content-end " >
                
                <a type="button" id="mail" class="btn btn-info mx-2" href="<?=dirname(FULL_BASE_URL,2)?>/crm.mail.list/index.php">
                    <i class="bi bi-envelope-fill" style="margin-right: 5px;"></i>MAIL
                </a>
                <a type="button" id="event" class="btn btn-info mx-2" href="<?=dirname(FULL_BASE_URL,2)?>/crm.event.list/index.php">
                    <i class="bi bi-calendar-fill" style="margin-right: 5px;"></i>AGENDA
                </a>
                <a type="button" id="alertesBodacc" class="btn btn-danger mx-2" href="<?=FULL_BASE_URL?>/company/bodacc-alerts">
                    <i class="bi bi-bell-fill" style="margin-right: 5px;"></i>ALERTES
                </a>
                <a type="button" id="bodacc" class="text-white btn btn-primary mx-2" href="https://www.bodacc.fr/pages/annonces-commerciales/?q.registre=registre:<?=$company["SIREN"]?>&refine.familleavis=collective#resultarea">
                    <i class="bi bi-file-text " ></i> BODACC
                </a>
                <?php if(!empty($company['pappersUrl'])): ?>
                    <a class="text-white btn btn-info mx-2 " type="button" href="<?php echo htmlspecialchars($company['pappersUrl']); ?>" target="_blank">
                        <i class="bi bi-bank" ></i> PAPPERS
                    </a>
                <?php endif; ?>
                <?php if(!empty($company['annuaireUrl'])): ?>
                    <a class="text-white btn btn-secondary mx-2" type="button" href="<?php echo htmlspecialchars($company['annuaireUrl']); ?>" target="_blank">
                        <i class="bi bi-buildings"></i> ANNUAIRE ENTREPRISE
                    </a>
                <?php endif; ?>
                <?php if(!empty($company['pagesJaunesUrl'])): ?>
                    <a class="text-white btn btn-warning mx-2" type="button" href="<?php echo htmlspecialchars($company['pagesJaunesUrl']); ?>" target="_blank">
                        <i class="bi bi-building-fill"></i> PAGES JAUNES
                    </a>
                <?php endif;?>
                <?php if(!empty($company['societe.comUrl'])): ?>
                    <a class="text-white btn btn-info mx-2 " type="button" href="<?php echo htmlspecialchars($company['societe.comUrl']); ?>" target="_blank">
                        <i class="bi bi-buildings-fill" ></i> SOCIETE.COM
                    </a>
                <?php endif;?>
                <?php 
                
                $fields=$company['fields']['bitrix']??[];
      
                if(!empty($fields) && !empty($company[$fields["NextcloudAccount"]]) && !empty($company[$fields["NextcloudPassword"]])): ?>
                <button id="nextcloud" class="btn btn-primary mx-2" >
                    <i class="bi bi-file-text" style="margin-right: 5px;"></i>Create Nextcloud Space
                </button>
                <?php endif;?>
                <!--div class="btn-group" role="group" aria-label="Actions">
                    <a href="/src/modules/crm.company.insee/index.php/webhook" type="button" class="btn btn-primary" id="saveWebhook">Configurer le Webhook</a>
                </div-->
            </div>
            
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script>
            $(document).ready(function() {

                // Fonction pour sauvegarder le webhook
                <?php if(!empty($fields) && !empty($company[$fields['NextcloudAccount']]) && !empty($company[$fields['NextcloudPassword']])): ?>
                    createUserSpace();
                    fetch("<?=FULL_BASE_URL?>/api/nextcloud/folder/find", {
                    method: 'POST',
                    body: JSON.stringify({
                        userId: "<?=($company[$fields['NextcloudAccount']])?>",
                        company: "<?=($companyLabel=$company['legalName'].$company['SIRET'])?>",
                        folderName: "/public/<?=$companyLabel?>"
                        })
                    }).then(function(response) {
                        return response.json();
                    }).then(function(data) {
                        if(data.status=="success"){
                            console.log("folderfound",data);
                            $('#nextcloud').text('Ouvrir le drive Nextcloud');
                            $('#nextcloud').unbind('click');
                            $('#nextcloud').click(function() {
                                window.open(data.data.folderUrl, '_blank');
                            });
                        }else{
                            console.log("Folder not found", data)
                        }
                        
                    }).catch(function(error) {
                        console.log("Error nextcloud in getting folder", error)
                    });
                <?php endif; ?>
                function createUserSpace(){
                    $('#nextcloud').click(function(){
                        const data={
                            userId: "<?=($company[$fields['NextcloudAccount']]) ?>",
                            password: "<?=($company[$fields['NextcloudPassword']]) ?>",
                            company: "<?=($companyLabel=$company['legalName'].$company['SIRET'])?>"
                        }
                        $.ajax({
                            url: "<?=FULL_BASE_URL?>/api/nextcloud/space/create",
                            method: 'POST',
                            data: data,
                            success: function(response) {
                               console.log("success data",response)
                                alert('Profil Nextcloud créé avec succès !');
                                $('#nextcloud').text('Ouvrir le drive');
                                $('#nextcloud').unbind('click');
                                console.log("nextcloud data",response.data)
                                $('#nextcloud').click(function() {
                                    window.open(response.data.folderPath, '_blank');
                                });
                            },
                            error: function(xhr, status, error) {
                                console.info("User space Info",data)
                                console.error("nextcloud status", status);
                                console.log("nextcloud error", xhr);
                                alert('Erreur lors de la creation du profil Nextcloud. Le mot de passe doit être au moins de 10 caractères.Erreur : '+error);
                            }
                        });
                    });
                }
                // $('#saveWebhook').click(function() {
                //     const webhook = prompt("Veuillez entrer le webhook Bitrix :");
                //     if (webhook) {
                //         $.ajax({
                //             url: '/api/webhook/save',
                //             method: 'POST',
                //             data: { webhook: webhook },
                //             success: function(response) {
                //                 alert('Webhook sauvegardé avec succès !');
                //                 location.reload();
                //             },
                //             error: function() {
                //                 alert('Erreur lors de la sauvegarde du webhook');
                //             }
                //         });
                //     }
                // });

                // // Fonction pour ajouter/consulter une entreprise
                // $('#addCompany').click(function() {
                //     const companyData = $(this).data('company');
                //     $.ajax({
                //         url: '/api/company/check',
                //         method: 'POST',
                //         data: companyData,
                //         success: function(response) {
                //             if (response.exists) {
                //                 $('#addCompany').hide();
                //                 $('#viewCompany').show()
                //                     .attr('href', response.url)
                //                     .click(function() {
                //                         window.location.href = response.url;
                //                     });
                //             } else {
                //                 $.ajax({
                //                     url: '/api/company/add',
                //                     method: 'POST',
                //                     data: companyData,
                //                     success: function(response) {
                //                         alert('Entreprise ajoutée avec succès !');
                //                         location.reload();
                //                     },
                //                     error: function() {
                //                         alert('Erreur lors de l\'ajout de l\'entreprise');
                //                     }
                //                 });
                //             }
                //         }
                //     });
                // });
            });
            </script>
        <?php else :?>  
            <!-- <iframe height="100vh" width="100%" id="iframe"src="templateblank.php" style="width: 100%; height: 100vh; border: none;"></iframe> -->
        <?php endif; ?>
    </div>
</div>
