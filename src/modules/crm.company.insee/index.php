<?php
// Charger l'autoload de Composer pour les dépendances externes

require_once dirname(__DIR__,3).'/vendor/autoload.php';
use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\CompanyComponent;

$NSCompanyPappers = new CompanyComponent();

$NSCompanyPappers
    ->setCustomSiret('751 376 559 00017')
    ->getCompanyFromAnnuaire()
    ->getCompanyFromInsee()
    ->getCompanyRequisite()
    ->getCompanyFromBodacc()
    ->getBodaccAlerts()
    ->redirectToPappers()
    ->renderCurrentCompany();

// Afficher les activités pour débogage

//TO DO LIST 
// get company name from insee from siret
// replace  blank space, underscore, ' by - -<siren>

?>
