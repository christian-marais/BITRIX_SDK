
<div class="card">
    <div class="card-body">
        <?php if(!empty($company['SIREN']) && $display=true): ?>
            <div class="card-title d-flex justify-content-end " >
                <button id="alerteBodacc" class="btn btn-primary mx-2" >
                    <i class="bi bi-file-text" style="margin-right: 5px;"></i>Alerte bodacc
                </button>
                <a type="button" id="bodacc" class="text-white btn btn-primary mx-2" href="https://www.bodacc.fr/pages/annonces-commerciales/?q.registre=registre:<?=$company["SIREN"]?>&refine.familleavis=collective#resultarea">
                    <i class="bi bi-file-text " ></i>BODACC
                </a>
                <?php if(!empty($company['pappersUrl'])): ?>
                    <a class="text-white btn btn-info mx-2 " type="button" href="<?php echo htmlspecialchars($company['pappersUrl']); ?>" target="_blank">
                        <i class="bi bi-file-pdf" ></i>PAPPERS
                    </a>
                <?php endif; ?>
                <?php if(!empty($company['annuaireUrl'])): ?>
                    <a class="text-white btn btn-secondary mx-2" type="button" href="<?php echo htmlspecialchars($company['annuaireUrl']); ?>" target="_blank">
                        <i class="bi bi-file-pdf" ></i>ANNUAIRE ENTREPRISE
                    </a>
                <?php endif; ?>
                <?php if(!empty($company['pagesJaunesUrl'])): ?>
                    <a class="text-white btn btn-info mx-2" type="button" href="<?php echo htmlspecialchars($company['pagesJaunesUrl']); ?>" target="_blank">
                        <i class="bi bi-file-pdf" ></i>PAGES JAUNES
                    </a>
                <?php endif;?>
                <?php if(!empty($company['societe.comUrl'])): ?>
                    <a class="text-white btn btn-info mx-2 " type="button" href="<?php echo htmlspecialchars($company['societe.comUrl']); ?>" target="_blank">
                        <i class="bi bi-file-pdf" ></i>SOCIETE.COM
                    </a>
                <?php endif;?>
                <!--div class="btn-group" role="group" aria-label="Actions">
                    <a href="/src/modules/crm.company.insee/index.php/webhook" type="button" class="btn btn-primary" id="saveWebhook">Configurer le Webhook</a>
                </div-->
            </div>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script>
            $(document).ready(function() {
                // Fonction pour sauvegarder le webhook
                $('#saveWebhook').click(function() {
                    const webhook = prompt("Veuillez entrer le webhook Bitrix :");
                    if (webhook) {
                        $.ajax({
                            url: '/api/webhook/save',
                            method: 'POST',
                            data: { webhook: webhook },
                            success: function(response) {
                                alert('Webhook sauvegardé avec succès !');
                                location.reload();
                            },
                            error: function() {
                                alert('Erreur lors de la sauvegarde du webhook');
                            }
                        });
                    }
                });

                // Fonction pour ajouter/consulter une entreprise
                $('#addCompany').click(function() {
                    const companyData = $(this).data('company');
                    $.ajax({
                        url: '/api/company/check',
                        method: 'POST',
                        data: companyData,
                        success: function(response) {
                            if (response.exists) {
                                $('#addCompany').hide();
                                $('#viewCompany').show()
                                    .attr('href', response.url)
                                    .click(function() {
                                        window.location.href = response.url;
                                    });
                            } else {
                                $.ajax({
                                    url: '/api/company/add',
                                    method: 'POST',
                                    data: companyData,
                                    success: function(response) {
                                        alert('Entreprise ajoutée avec succès !');
                                        location.reload();
                                    },
                                    error: function() {
                                        alert('Erreur lors de l\'ajout de l\'entreprise');
                                    }
                                });
                            }
                        }
                    });
                });
            });
            </script>
        <?php else :?>  
            <iframe height="100vh" width="100%" id="iframe"src="templateblank.php" style="width: 100%; height: 100vh; border: none;"></iframe>
        <?php endif; ?>
    </div>
</div>
