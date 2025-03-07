<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boîte Mail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../base/assets/css/style.php?lang=en" rel="stylesheet" type="text/css" media="all" />
</head>
<body>
<?php if(!empty($company['COMPANY_PAPERS_URL'])): ?>
    <div class="card">
        <div class="card-body">
            <?php if(!empty($company['COMPANY_SIREN'])): ?>
                <div class="card-title d-flex justify-content-end mx-3" >
                    <button id="bodacc" class="btn btn-primary " type="button" >
                        BODACC
                    </button>
                </div>
            <?php endif; ?>
            <iframe src="<?php echo htmlspecialchars('https://www.societe.com/societe/ace-expert-oi-848342093.html'); ?>" style="width: 100%; height: 100vh; border: none;"></iframe>
        </div>
    </div>
   
<?php endif; ?>
<?php if(!empty($company['COMPANY_SIREN'])): ?>
    <script type="text/javascript" src="./../base/assets/js/slider.js"></script>
    <script type="text/javascript">
        setBitrix24Slider('#bodacc',  '<?php echo htmlspecialchars($company['COMPANY_SIREN']); ?>');
    </script>
<?php endif; ?>
</body>
</html>
