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
<body><?=include dirname(__FILE__) . 'snippets/buttons.php'; ?>
<?php if(!empty($company['pappers']) && $display=false): ?>
    <div class="card">
        <div class="card-body">
            <?php if(!empty($company['SIREN'])): ?>
                <div class="card-title d-flex justify-content-end mx-3" >
                    <button id="bodacc" class="btn btn-primary" style="line-height: 1.5rem; padding: 0.5rem 1.5rem; border-radius: 5px; transition: background-color 0.3s; box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);">
                        <i class="bi bi-file-text" style="margin-right: 5px;"></i>BODACC
                    </button>
                </div>
            <?php endif; ?>
            <iframe id="iframe" src="<?php echo htmlspecialchars($company['pappers']); ?>" style="width: 100%; height: 100vh; border: none;"></iframe>
        </div>
    </div>
   
<?php endif; ?>
<?php if(!empty($company['SIREN'])): ?>
    <script type="text/javascript" src="./../base/assets/js/slider.js"></script>
    <script type="text/javascript"> 
        setBitrix24Slider('#bodacc',  '<?php echo htmlspecialchars($company['SIREN']); ?>');
    </script>
<?php endif; ?>
</body>
</html>
