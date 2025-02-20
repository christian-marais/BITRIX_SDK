<?php
declare(strict_types=1);

use Bitrix24\SDK\Services\ServiceBuilderFactory;

// ensure the path to autoload.php is correct. it may differ if
// you are using a different folder structure 
require_once dirname(__DIR__).'/vendor/autoload.php'; 

$B24 = ServiceBuilderFactory::createServiceBuilderFromWebhook(
    'https://bitrix24demoec.ns2b.fr/rest/12/2neihcmydm0tpxux/'
);

$result = $B24->getCRMScope()->deal();
print_r($result);
