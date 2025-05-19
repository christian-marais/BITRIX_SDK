<?php
// Charger l'autoload de Composer pour les dépendances externes
require_once dirname(__DIR__,3).'/vendor/autoload.php';

use NS2B\SDK\MODULES\CRM\COMPANY\STORAGE\ROUTES\WEB\WebRouteProvider;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

define("MODULE_DIR",dirname(__DIR__).'/');
define("B24_DOMAIN","https://bitrix24demoec.ns2b.fr");
define ("FULL_BASE_URL",'//'.$request->server->get("HTTP_HOST").$request->server->get("SCRIPT_NAME"));
define("BASE_URL",$request->server->get("SCRIPT_NAME").'/'??'/src/modules/crm.company.storage/index.php/');
const TEMPLATE_DIR=MODULE_DIR.'crm.company.storage/templates/';
const DEBUG = false;
const IS_B24_IMPLEMENTED=false;
const DISABLE_FIREWALL='DISABLE_FIREWALL';
// const BASE_URL='/src/modules/crm.company.insee/index.php/';


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
