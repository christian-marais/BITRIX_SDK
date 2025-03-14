<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
                            <button class="btn btn-primary">Ajouter l'entreprise</button>
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
                <?php foreach ($company["annuaire"]->dirigeants as $dirigeant): ?>
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
                        <button class="btn btn-sm btn-primary" onclick="window.location.href=''">Ajouter le contact</button>
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
                            <p><span class="fw-bold badge bg-dark">Jugement: </span>   <?php echo $record["jugement"]; ?></p>
                            <p><span class="fw-bold badge bg-dark"> Date du jugement :</span>   <?php echo $record["datejugement"]; ?></p>
                            
                            
                            <a class="btn btn-primary" title="consulter l'annonce" href="<?php echo $record['url_complete'];?>">Consulter l'annonce</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>