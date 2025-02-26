<?php
// Charger l'autoload de Composer pour les dépendances externes

require_once dirname(__DIR__,2).'/vendor/autoload.php';

$componentPath = dirname(__DIR__,2).'/modules/crm.company.pappers/component.php';

// Vérifier si le fichier existe
if (!file_exists($componentPath)) {
    die("Fichier de composant non trouvé : $componentPath");
}

require_once($componentPath);

$NSCompanyPappers = new NS2B\NSCompanyPappers();

// Gestion des paramètres de pagination
$itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

$NSCompanyPappers
    ->checkRequiredScopes()
    ->setItemsPerPage($itemsPerPage)
    ->setCurrentPage($currentPage)
    ->getCurrentCompany()
    ->getCompanyRequisite()
    ->redirectToPappers();

// Afficher les activités pour débogage
?>
