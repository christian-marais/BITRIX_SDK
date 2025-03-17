<?php
// Charger l'autoload de Composer pour les dépendances externes
require_once dirname(__DIR__,3).'/vendor/autoload.php';

use NS2B\SDK\DATABASE\DatabaseSQLite;
use NS2B\SDK\MODULES\BASE\WebhookManager;
use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\WEB\WebRouteProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

const BASE_URL='/src/modules/crm.company.insee/index.php/';
define("MODULE_DIR",dirname(__DIR__).'/');
const TEMPLATE_DIR=MODULE_DIR.'crm.company.insee/templates/';

// // Si aucune route n'est trouvée, continuer avec le code existant
//     $database = new DatabaseSQLite();
//     $webhookManager = new WebhookManager($database);
//     $webhookManager
//         ->renderHome()
//         ->askWebhook()
//         ->render();
// die();
// Créer les instances des fournisseurs de routes
$webRouteProvider = new WebRouteProvider();
$request = Request::createFromGlobals();
try {
    // Tenter d'abord de gérer les routes web
    $response = $webRouteProvider->launch($request);
    $response->send();
    exit;
} catch (ResourceNotFoundException $e) {
    
   
}
