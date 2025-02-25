<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Activités Mail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <link href="../../base/assets/css/style.php?lang=en" rel="stylesheet" type="text/css" media="all" />
</head>
<body>
    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0">
                            <i class="bi bi-envelope-paper me-2"></i>Activités Mail
                        </h2>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="bi bi-filter me-1"></i>Filtrer
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="exportBtn">
                                <i class="bi bi-download me-1"></i>Exporter
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="resetFiltersBtn">
                                <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <label for="itemsPerPage" class="my-2 mx-2 d-inline-block">Mails par page :</label>
                                <select id="itemsPerPage" class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='?itemsPerPage=' + this.value">
                                    <?php 
                                    $pageSizes = [1,10, 25, 50, 100];
                                    $currentItemsPerPage = $activityCollection->pagination['itemsPerPage'];
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
                                        <th class="text-center" style="width: 50px;">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="selectAll" role="switch">
                                                <label class="form-check-label" for="selectAll"></label>
                                            </div>
                                        </th>
                                        <th>ID</th>
                                        <th>Sujet</th>
                                        <th>Date de Création</th>
                                        <th>Dernière Mise à Jour</th>
                                        <th>Responsable</th>
                                        <th>Statut</th>
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
                                    <tr>
                                        <td class="text-center">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" value="<?php echo htmlspecialchars($activity['ID']); ?>" role="switch">
                                            </div>
                                        </td>
                                        <td id="activityID_<?php echo $activity['ID']; ?>"><?php echo htmlspecialchars($activity['ID']); ?></td>
                                        <td id="activitySubject_<?php echo $activity['ID']; ?>">
                                            <a href="#" data-id="<?php echo $activity['ID']; ?>" class="text-primary text-decoration-none activityLink" data-bs-toggle="modal" data-bs-target="#activityModal<?php echo $activity['ID']; ?>">
                                                <i class="bi bi-envelope-open me-2"></i><?php echo htmlspecialchars($activity['SUBJECT']); ?>
                                            </a>
                                        </td>
                                        <td id="activityCreatedDate_<?php echo $activity['ID']; ?>"><?php echo date('d/m/Y H:i', strtotime($activity['CREATED'])); ?></td>
                                        <td id="activityUpdatedDate_<?php echo $activity['ID']; ?>"><?php echo date('d/m/Y H:i', strtotime($activity['LAST_UPDATED'])); ?></td>
                                        <td data-id="<?php echo $activity['RESPONSIBLE_ID']; ?>" id="activityResponsible_<?php echo $activity['ID']; ?>"><?php echo htmlspecialchars(($activity["responsible"]["LAST_NAME"]??'').' '.($activity["responsible"]["NAME"]??$activity['RESPONSIBLE_ID'])); ?></td>
                                        <td>
                                            <?php 
                                            $status = $activity['COMPLETED'] === 'Y' ? 
                                                '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Terminé</span>' : 
                                                '<span class="badge bg-warning"><i class="bi bi-clock me-1"></i>En cours</span>'; 
                                            echo $status; 
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
                                sur <?php echo $activityCollection->pagination['total']; ?> mails
                            </div>
                            <nav aria-label="Navigation des pages">
                                <ul class="pagination mb-0">
                                    <li class="page-item <?php echo $activityCollection->pagination['currentPage'] <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $activityCollection->pagination['currentPage'] - 1; ?>&itemsPerPage=<?php echo $currentItemsPerPage; ?>" aria-label="Précédent">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php for($i = 1; $i <= max(1, $activityCollection->pagination['totalPages']); $i++): ?>
                                        <li class="page-item <?php echo $activityCollection->pagination['currentPage'] == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&itemsPerPage=<?php echo $currentItemsPerPage; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $activityCollection->pagination['currentPage'] >= $activityCollection->pagination['totalPages'] ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $activityCollection->pagination['currentPage'] + 1; ?>&itemsPerPage=<?php echo $currentItemsPerPage; ?>" aria-label="Suivant">
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
                            <select id="statusFilter" multiple>
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

    <script type="text/javascript" src="./../base/assets/js/main.js"></script>
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
    ;
    if (!empty($errorMessages)) {
        
        echo "document.addEventListener('DOMContentLoaded', function() {";
        foreach ($errorMessages as $errorMessage) {    
            echo "showAlert('" . addslashes($errorMessage) . "', 'danger')";
        }
        echo "});";
    }
    ?>
</body>
</html>
