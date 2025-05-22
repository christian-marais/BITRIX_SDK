<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertes BODACC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .info-block {
            background: white;
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .info-block:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .info-header {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .info-header h2 {
            color: #dc3545;
            font-weight: 600;
        }
        .info-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            padding: 8px 0;
        }
        .info-label {
            flex: 0 0 180px;
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            flex: 1;
            color: #212529;
        }
        .alert-custom {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(220,53,69,0.2);
        }
        .btn-consult {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .btn-consult:hover {
            background-color: #0b5ed7;
            transform: translateY(-1px);
        }
        .date-badge {
            background-color: #6c757d;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .company-badge {
            background-color:rgb(62, 136, 201);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="py-4">
<div class="container">
    <?php global $request;if($back=$request->server->get('HTTP_REFERER')): ?>
    <div class="btn btn-info"><a class="text-decoration-none text-white" type="button" style="font-weight:bold;"href="<?=$back?>"><i class="bi bi-arrow-left"></i> Retour</a></div>
    <?php endif; ?>
    <a type="button" id="search" class="text-white btn btn-primary mx-2" href="<?=FULL_BASE_URL?>/company/">
        <i class="bi bi-search me-2" ></i> Société
    </a>
    <a type="button" id="parameter" class="text-white btn btn-primary mx-2" href="<?=FULL_BASE_URL.'/webhook'?>">
        <i class="bi bi-gear " ></i> Paramètres
    </a>
    <hr>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <?php if (empty($bodaccAlerts)): ?>
                <div class="alert alert-info shadow-sm" role="alert">
                    <i class="bi bi-info-circle me-2"></i>Aucune alerte BODACC trouvée.
                </div>
            <?php else: ?>
                <div class="alert alert-custom">
                    <h4 class="alert-heading mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo count($bodaccAlerts); ?> alerte<?php echo count($bodaccAlerts) > 1 ? 's' : ''; ?> BODACC détectée<?php echo count($bodaccAlerts) > 1 ? 's' : ''; ?>
                    </h4>
                </div>
                <?php foreach ($bodaccAlerts as $record): ?>
                    <div class="info-block">
                        <div class="info-header d-flex justify-content-between align-items-center">
                            <h2 class="h5 mb-0">
                                <i class="bi bi-file-text me-2"></i>
                                <?php echo htmlspecialchars($record["TITLE"] ?? ''); ?>
                                <span class="company-badge">
                                <i class="bi bi-3d me-1"></i>
                                <?php echo "N° <a class='text-decoration-none text-white' href='".B24_DOMAIN."/crm/company/details/".htmlspecialchars($record["COMPANY_ID"])."/'>".htmlspecialchars($record["COMPANY_ID"]); ?>
                                </a>
                            </span>
                            </h2>
                            
                             <span class="date-badge">
                                <i class="bi bi-calendar-event me-1"></i> Parue le
                                <?php echo htmlspecialchars($record["dateparution"]); ?>
                            </span>
                        </div>
                        <div class="info-content">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-building me-2"></i>Siren
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($record["siren"]); ?>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-card-text me-2"></i>Description
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($record["contenu"]); ?>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-journal-text me-2"></i>Jugement
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($record["jugement"]); ?>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-calendar2-check me-2"></i>Date du jugement
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($record["datejugement"]); ?>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-calendar2-check me-2"></i>Date du parution
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($record["dateparution"]); ?>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <a class="btn btn-consult" href="<?php echo htmlspecialchars($record['url']); ?>" target="_blank">
                                    <i class="bi bi-box-arrow-up-right me-2"></i>Consulter l'annonce
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
