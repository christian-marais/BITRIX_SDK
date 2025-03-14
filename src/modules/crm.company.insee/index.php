<?php
// Charger l'autoload de Composer pour les dépendances externes

require_once dirname(__DIR__,3).'/vendor/autoload.php';
use NS2B\SDK\DATABASE\DatabaseSQLite;
use NS2B\SDK\MODULES\BASE\WebhookManager;

// Vérifier si c'est une requête API
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    require_once __DIR__ . '/api/routes.php';
    exit;
}

// $database = new DatabaseSQLite();
// $webhookManager = new WebhookManager($database);
// $webhookManager
//     ->renderHome()
//     ->askWebhook()
//     ->render();

$component = new NS2B\SDK\MODULES\CRM\COMPANY\INSEE\CompanyComponent();

$component
    // ->setCustomSiret('751 376 559 00017')
    ->getCompanyFromAnnuaire()
    ->getCompanyFromInsee()
    ->getCompanyRequisite()
    ->getCompanyFromBodacc()
    ->getBodaccAlerts()
    // ->redirectToPappers()
    ->renderCurrentCompany();
