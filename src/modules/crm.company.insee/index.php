<?php
// Charger l'autoload de Composer pour les dépendances externes
require_once dirname(__DIR__,3).'/vendor/autoload.php';

use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\WEB\WebRouteProvider;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
define('HTTPS',$request?->server->get("SERVER_PORT")=='443'?'https://':'http://');
define("MODULE_DIR",dirname(__DIR__).'/');
define("B24_DOMAIN","https://bitrix24demoec.ns2b.fr");
define ("FULL_BASE_URL",HTTPS.$request->server->get("HTTP_HOST").$request->server->get("SCRIPT_NAME"));
define("BASE_URL",$request->server->get("SCRIPT_NAME").'/'??'/src/modules/crm.company.insee/index.php/');
const TEMPLATE_DIR=MODULE_DIR.'crm.company.insee/templates/';
const DEBUG = false;
const IS_B24_IMPLEMENTED=false;
// const BASE_URL='/src/modules/crm.company.insee/index.php/';


$webRouteProvider = new WebRouteProvider();
try {
    $response = $webRouteProvider->launch($request);
    $response->send();
    exit;
} catch (\Exception $e) {
    echo $e->getMessage();
   
}
function _error_log($log){
    if(defined('DEBUG')&&DEBUG)
    {
        error_log($log);
    }
}
