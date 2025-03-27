<?php
// Charger l'autoload de Composer pour les dépendances externes
require_once dirname(__DIR__,3).'/vendor/autoload.php';
use NS2B\SDK\MODULES\CRM\CHAT\RSS\ChatComponent;

$NSChatActivity = new ChatComponent();

// // Gestion des paramètres de pagination
// $itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
// $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

$res=$NSChatActivity
    ->checkRequiredScopes()
    ->setItemsPerPage($itemsPerPage)
    ->setCurrentPage($currentPage)
    ->getRss()

    // ->getActivities()
    // ->renderActivitiesList()
    ;
var_dump($res);die();
// Afficher les activités pour débogage
?>


