<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <link href="../base/assets/css/style.css?lang=en" rel="stylesheet" type="text/css" media="all" />
</head>
<body>
    <div class="container-fluid px-4 py-4">
       
        <div class="card-header mb-4"> 
            <?php global $request;if($back=$request->server->get('HTTP_REFERER')): ?>
            <div class="btn btn-info"><a class="text-decoration-none text-white" type="button" style="font-weight:bold;"href="<?=$back?>"><i class="bi bi-arrow-left"></i> Retour</a></div>
            <?php endif; ?>
            <div class="btn btn-primary"><a type="button" id="bodacc" class="text-decoration-none text-white" href="<?=dirname(FULL_BASE_URL,2).'/crm.company.insee/index.php/company/'?>">
                <i class="bi bi-house " ></i> HOME
            </a></div>
            <hr>
        </div>
    
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0">
                            <i class="bi bi-calendar-event me-2"></i>Rendez-vous
                        </h2>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="bi bi-filter me-1"></i>Filtrer
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="exportBtn">
                                <i class="bi bi-download me-1"></i>Exporter
                            </button>
                           
                            <button type="button" class="btn btn-outline-danger btn-sm" id="resetFiltersBtn" onclick="resetFilters()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <label for="itemsPerPage" class="my-2 mx-2 d-inline-block">Rendez-vous par page :</label>
                                <select id="itemsPerPage" class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='?itemsPerPage=' + this.value">
                                    <?php 
                                    
                                    $currentItemsPerPage = $activityCollection->pagination['itemsPerPage'];
                                    $pageSizes = array_unique([$currentItemsPerPage??10,1,5,25,50, 100]);
                                    sort($pageSizes);
                                    foreach ($pageSizes as $size): 
                                    ?>
                                        <option value="<?php echo $size; ?>" <?php echo ($size == $currentItemsPerPage) ? 'selected' : ''; ?>>
                                            <?php echo $size; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="activitiesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center sortable" data-sort="checkbox" style="width: 50px;">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="selectAll" role="switch">
                                                <label class="form-check-label" for="selectAll"></label>
                                            </div>
                                        </th>
                                        <th class="sortable" data-sort="id">ID <i class="bi bi-arrow-down-up"></i></th>
                                        <th class="sortable" data-sort="subject">Sujet <i class="bi bi-arrow-down-up"></i></th>
                                        <th class="sortable" data-sort="created">Date de Création <i class="bi bi-arrow-down-up"></i></th>
                                        <th class="sortable" data-sort="updated">Dernière Mise à Jour <i class="bi bi-arrow-down-up"></i></th>
                                        <th class="sortable" data-sort="responsible">Responsable <i class="bi bi-arrow-down-up"></i></th>
                                        <th class="sortable" data-sort="completed">Statut <i class="bi bi-arrow-down-up"></i></th>
                                        <th class="sortable" data-sort="location">Lieu <i class="bi bi-arrow-down-up"></i></th>
                                        <th class="sortable" data-sort="collaborators">Collaborateurs <i class="bi bi-arrow-down-up"></i></th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $count = 0;
                                    foreach ($activities as $activity): 
                                        if ($count >= $activityCollection->pagination['itemsPerPage']) break;
                                        $count++;
                                    ?>
                                    <tr data-activity-id="<?= htmlspecialchars($activity['ID']) ?>">
                                        <td class="text-center">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" value="<?php echo htmlspecialchars($activity['ID']); ?>" role="switch">
                                            </div>
                                        </td>
                                        <td id="activityID_<?= htmlspecialchars($activity['ID']) ?>"><?= htmlspecialchars($activity['ID']) ?></td>
                                        <td id="activitySubject_<?= htmlspecialchars($activity['ID']) ?>">
                                            <a href="#" data-id="<?php echo $activity['ID']; ?>" class="text-primary text-decoration-none activityLink">
                                                <i class="bi bi-calendar-event me-2"></i><?php echo htmlspecialchars($activity['SUBJECT']); ?>
                                            </a>
                                        </td>
                                        <td id="activityCreatedDate_<?= htmlspecialchars($activity['ID']) ?>"><?= date('d/m/Y H:i', strtotime($activity['CREATED'])); ?></td>
                                        <td id="activityUpdatedDate_<?= htmlspecialchars($activity['ID']) ?>"><?= date('d/m/Y H:i', strtotime($activity['LAST_UPDATED'])); ?></td>
                                        <td id="activityResponsible_<?= htmlspecialchars($activity['ID']) ?>" data-id="<?php echo $activity['RESPONSIBLE_ID']; ?>"><?php echo htmlspecialchars(($activity["responsible"]["LAST_NAME"]??'').' '.($activity["responsible"]["NAME"].'['.$activity['RESPONSIBLE_ID']).']'); ?></td>
                                      
                                        <td>
                                            <?php 
                                            $completed = $activity['COMPLETED'] === 'Y' ? 
                                                '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Terminé</span>' : 
                                                '<span class="badge bg-warning"><i class="bi bi-clock me-1"></i>En cours</span>'; 
                                            echo $completed; 
                                            ?>
                                        </td>
                                        <td id="activityLocation_<?= htmlspecialchars($activity['ID']) ?>"><?= htmlspecialchars($activity['LOCATION']); ?></td>
                                        <td id="activityCollaborators_<?= htmlspecialchars($activity['ID']) ?>" data-collaborators="<?php 
                                            $collaboratorIds = [];
                                            if (isset($activity['COWORKERS']) && is_array($activity['COWORKERS'])) {
                                                foreach ($activity['COWORKERS'] as $coworker) {
                                                    $collaboratorIds[] = $coworker['ID'];
                                                }
                                            }
                                            echo htmlspecialchars(implode(',', $collaboratorIds));
                                        ?>">
                                            <?php 
                                            if (isset($activity['COWORKERS']) && is_array($activity['COWORKERS'])) {
                                                foreach ($activity['COWORKERS'] as $coworker) {
                                                    $fullName = htmlspecialchars($coworker['LAST_NAME'] . ' ' . $coworker['NAME']);
                                                    $coworkerId = htmlspecialchars($coworker['ID']);
                                                    echo "<span class='badge bg-secondary me-1 coworker-badge' data-coworker-id='{$coworkerId}'>{$fullName} <i class='bi bi-x-circle-fill text-white ms-1 remove-coworker-filter d-none'></i></span>";
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#activityModal<?php echo $activity['ID']; ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal pour les détails de l'activité -->
                                    <div class="modal fade" id="activityModal<?php echo $activity['ID']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <i class="bi bi-envelope-paper me-2"></i>Détails de l'activité #<?php echo $activity['ID']; ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <div class="card h-100">
                                                                <div class="card-header">
                                                                    <strong><i class="bi bi-info-circle me-2"></i>Informations Générales</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                    <p><strong>Sujet :</strong> <?php echo htmlspecialchars($activity['SUBJECT']); ?></p>
                                                                    <p><strong>Date de création :</strong> <?php echo date('d/m/Y H:i', strtotime($activity['CREATED'])); ?></p>
                                                                    <p><strong>Dernière mise à jour :</strong> <?php echo date('d/m/Y H:i', strtotime($activity['LAST_UPDATED'])); ?></p>
                                                                    <p><strong>Début :</strong> <?php echo date('d/m/Y H:i', strtotime($activity['START_TIME'])); ?></p>
                                                                    <p><strong>Fin :</strong> <?php echo date('d/m/Y H:i', strtotime($activity['END_TIME'])); ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card h-100">
                                                                <div class="card-header">
                                                                    <strong><i class="bi bi-person-check me-2"></i>Détails Supplémentaires</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                    <p><strong>Statut :</strong> <?php echo $activity['COMPLETED'] === 'Y' ? 'Terminé' : 'En cours'; ?></p>
                                                                    <p><strong>ID Responsable :</strong> <?php echo htmlspecialchars($activity['RESPONSIBLE_ID']); ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <strong><i class="bi bi-file-text me-2"></i>Description</strong>
                                                                </div>
                                                                <div class="card-body description-section">
                                                                      <?php 
                                                                    $cleanDescription = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $activity['DESCRIPTION']);
                                                                    $cleanDescription = htmlspecialchars(preg_replace('/<!DOCTYPE [^>]+>|<[^>]+>/i', '', preg_replace('/<br\s*\/?>/i', "\n", $cleanDescription)));
                                                                    echo strlen($cleanDescription) > 500 ? 
                                                                        substr($cleanDescription, 0, 500) . '...' : 
                                                                        $cleanDescription; 
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="bi bi-x-circle me-2"></i>Fermer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <div class="text-muted">
                                Affichage de <?php echo (($activityCollection->pagination['currentPage'] - 1) * $activityCollection->pagination['itemsPerPage'] + 1); ?> 
                                à <?php echo min($activityCollection->pagination['currentPage'] * $activityCollection->pagination['itemsPerPage'], $activityCollection->pagination['total']); ?> 
                                sur <?php echo $activityCollection->pagination['total']; ?> rendez-vous
                            </div>
                            <nav aria-label="Navigation des pages">
                                <ul class="pagination mb-0">
                                    <li class="page-item <?php echo $activityCollection->pagination['currentPage'] <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" data-page="<?php echo $activityCollection->pagination['currentPage'] - 1; ?>" href="?page=<?php echo $activityCollection->pagination['currentPage'] - 1; ?>&itemsPerPage=<?php echo $currentItemsPerPage; ?>" aria-label="Précédent">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php for($i = 1; $i <= max(1, $activityCollection->pagination['totalPages']); $i++): ?>
                                        <li class="page-item <?php echo $activityCollection->pagination['currentPage'] == $i ? 'active' : ''; ?>">
                                            <a class="page-link" data-page="<?php echo $i; ?>" href="?page=<?php echo $i; ?>&itemsPerPage=<?php echo $currentItemsPerPage; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $activityCollection->pagination['currentPage'] >= $activityCollection->pagination['totalPages'] ? 'disabled' : ''; ?>">
                                        <a class="page-link" data-page="<?php echo $activityCollection->pagination['currentPage'] + 1; ?>" href="?page=<?php echo $activityCollection->pagination['currentPage'] + 1; ?>&itemsPerPage=<?php echo $currentItemsPerPage; ?>" aria-label="Suivant">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <div class="text-muted mb-2">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Filtres -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-funnel me-2"></i>Filtres Activités Mail
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="filterForm">
                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <select id="completedFilter" multiple>
                                <option value="Y">Terminé</option>
                                <option value="N">En cours</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Période</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="startDateFilter">
                                <span class="input-group-text">à</span>
                                <input type="date" class="form-control" id="endDateFilter">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Responsable</label>
                            <select id="responsibleFilter" multiple>
                                <?php 
                                // $responsibles = array_unique(array_column($activities, 'RESPONSIBLE_ID'));
                                $responsibles=array_unique($responsibles);
                                foreach ($responsibles as $responsible): 
                                ?>
                                <option value="<?php echo htmlspecialchars($responsible['ID']); ?>">
                                    Responsable #<?php echo htmlspecialchars($responsible['ID'].' '.$responsible["LAST_NAME"].' '.$responsible['NAME']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Objet</label>
                            <textarea rows="1" type="text" class="form-control" id="subjectFilter" placeholder="Tapez votre recherche..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="locationFilter" class="form-label">Lieu</label>
                            <select id="locationFilter" name="location" class="form-select" multiple data-placeholder="Sélectionner un ou plusieurs lieux">
                                <?php 
                                $locations = array_unique(array_filter(array_column($activityCollection->activities, 'LOCATION')));
                                foreach ($locations as $location): 
                                ?>
                                <option value="<?php echo htmlspecialchars($location); ?>">
                                    <?php echo htmlspecialchars($location); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="collaboratorsModalFilter" class="form-label">Collaborateurs</label>
                            <select id="collaboratorsModalFilter" name="collaborators" class="form-select" multiple data-placeholder="Sélectionner un ou plusieurs collaborateurs">
                                <?php foreach ($coworkers as $coworker): ?>
                                <option value="<?php echo htmlspecialchars($coworker['ID']); ?>">
                                    <?= htmlspecialchars($coworker['LAST_NAME'].' '.$coworker['NAME']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Annuler
                    </button>
                    <button type="button" class="btn btn-primary" id="applyFilters">
                        <i class="bi bi-check-circle me-2"></i>Appliquer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://unpkg.com/@bitrix24/b24jssdk@latest/dist/umd/index.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>

    <script type="text/javascript" src="../base/assets/js/main.js"></script>
    <script type="text/javascript" src="./assets/js/eventList.js"></script>
    <!-- Ajout du code pour les pop-ups d'alerte -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"  id="alertModalLabel">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="alertModalBody">
                    <!-- Contenu de l'alerte -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Vérification des scopes
       
    if (!empty($errorMessages)) {
        echo "<script>document.addEventListener('DOMContentLoaded', function() {";
            echo "showAlert('" . addslashes(implode(",",$errorMessages)) . "', 'danger');";
        echo "});managePagination();</script>";
        
    }
    ?>
</body>
</html>
