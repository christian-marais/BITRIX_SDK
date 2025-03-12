
<div class="card">
    <div class="card-body">
        <?php if(!empty($company['SIREN']) && $display=true): ?>
            <div class="card-title d-flex justify-content-end " >
                <button id="updateCompany" class="btn btn-primary mx-2" >
                    <i class="bi bi-file-text" style="margin-right: 5px;"></i>Importer l'Entreprise
                </button>
                <button id="alerteBodacc" class="btn btn-primary mx-2" >
                    <i class="bi bi-file-text" style="margin-right: 5px;"></i>Alerte bodacc
                </button>
                <button id="bodacc" class="btn btn-primary mx-2" >
                    <i class="bi bi-file-text " ></i>BODACC
                </button>
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
            </div>
            <?php else :?>  
            <iframe height="100vh" width="100%" id="iframe"src="templateblank.php" style="width: 100%; height: 100vh; border: none;"></iframe>
        <?php endif; ?>
    </div>
</div>
   
