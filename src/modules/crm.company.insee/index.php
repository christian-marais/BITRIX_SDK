<?php
// Charger l'autoload de Composer pour les dépendances externes
require_once dirname(__DIR__,3).'/vendor/autoload.php';

use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\WEB\WebRouteProvider;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

define("MODULE_DIR",dirname(__DIR__).'/');
define ("FULL_BASE_URL",'//'.$request->server->get("HTTP_HOST").$request->server->get("SCRIPT_NAME"));
const TEMPLATE_DIR=MODULE_DIR.'crm.company.insee/templates/';
const BASE_URL='/src/modules/crm.company.insee/index.php/';
const DEBUG = false;

$webRouteProvider = new WebRouteProvider();
try {
    // Tenter d'abord de gérer les routes web
    $response = $webRouteProvider->launch($request);
    $response->send();
    exit;
} catch (\Exception $e) {
    echo $e->getMessage();
   
}
function _error_log($log){
 if(defined('DEBUG'))
{
    error_log($log);
}}
