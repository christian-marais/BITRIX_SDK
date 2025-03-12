<?php
// Charger l'autoload de Composer pour les dépendances externes
require_once dirname(__DIR__,3).'/vendor/autoload.php';
use NS2B\SDK\MODULES\CRM\MAIL\LIST\ContactActivityComponent;

$NSContactMailActivity = new ContactActivityComponent();

// // Gestion des paramètres de pagination
// $itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
// $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

$NSContactMailActivity
    ->checkRequiredScopes()
    ->setItemsPerPage($itemsPerPage)
    ->setCurrentPage($currentPage)
    ->getActivities()
    ->renderActivitiesList();

// Afficher les activités pour débogage
?>
