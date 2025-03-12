<?php
// Charger l'autoload de Composer pour les dépendances externes

require_once dirname(__DIR__,2).'/vendor/autoload.php';

$componentPath = str_replace("\\","/",dirname(__FILE__).'/component.php');

// Vérifier si le fichier existe
if (!file_exists($componentPath)) {
    die("Fichier de composant non trouvé : $componentPath");
}

require_once($componentPath);

$NSCompanyPappers = new NS2B\NSCompanyPappers();



if (isset($_GET['siret'])) {
    $siret = $_GET['siret'];
    $companyData = $NSCompanyPappers->fetchCompanyData($siret);
    if ($companyData) {
        // Process and display the company data as needed
        // This can be adapted to fit the existing structure of your application
    }
}

$NSCompanyPappers
    ->setCustomSiret('92258711800014')
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
