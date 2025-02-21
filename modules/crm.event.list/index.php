<?php
// Charger l'autoload de Composer pour les dépendances externes
require_once dirname(__DIR__,2).'/vendor/autoload.php';

$componentPath = dirname(__DIR__,2).'/modules/crm.event.list/component.php';

// Vérifier si le fichier existe
if (!file_exists($componentPath)) {
    die("Fichier de composant non trouvé : $componentPath");
}

require_once($componentPath);

$NSContactEventActivity = new NS2B\NSContactEventActivity();

// Gestion des paramètres de pagination
$itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

$NSContactEventActivity
    ->checkRequiredScopes()
    ->setItemsPerPage($itemsPerPage)
    ->setCurrentPage($currentPage)
    ->getActivities()
    ->renderActivitiesList();

// Afficher les activités pour débogage
?>
