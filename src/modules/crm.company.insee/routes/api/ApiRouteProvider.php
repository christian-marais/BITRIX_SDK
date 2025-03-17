<?php
namespace NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\CompanyComponent;
use NS2B\SDK\DATABASE\DatabaseSQLite;
use NS2B\SDK\MODULES\BASE\WebhookManager;

class ApiRouteProvider
{
    private $routes;
    private $baseRoute = '/src/modules/crm.company.insee/index.php/';
    private $component;
    private $db;
    private $webhookManager;
    private $company;
    private $hashedWebhook;

    public function __construct()
    {
        $this->routes = new RouteCollection();
        $this->component = new CompanyComponent();
        $this->db = new DatabaseSQLite();
        $this->webhookManager = new WebhookManager($this->db);
        $this->hashedWebhook = $this->webhookManager->getWebhook();
        $this->populateCompany();
        $this->defineRoutes();
    }

    private function populateCompany(){
        $this->component = new CompanyComponent();
        $this->company = $this->component
        ->getCompanyFromAnnuaire()
        ->getCompanyFromInsee()
        ->getCompanyFromBodacc()
        ->getBodaccAlerts()
        ->getCompany()
        ;
    }

    private function defineRoutes()
    {
        // Route pour ajouter une entreprise
        $this->routes->add('api_add_company', new Route(
            $this->baseRoute . 'api/company/{siret}/save',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::saveCompany',
                'methods' => ['POST'],
                'company' => $this->company
            ]
        ));

        // Route pour récupérer une entreprise
        $this->routes->add('api_get_company', new Route(
            'api/company/{siret}/',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::getCompany',
                'methods' => ['GET']
            ]
        ));

        // Route pour mettre à jour une entreprise
        $this->routes->add('api_update_company', new Route(
          'api/company/{siret}/update',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::updateCompany',
                'methods' => ['PUT'],
                'company' => $this->company
            ]
        ));
      

        // Route pour récupérer le webhook
        $this->routes->add('api_get_webhook', new Route(
            '/api/webhook',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::getWebhook',
                'methods' => ['GET','POST'],
                'webhook' => $this->hashedWebhook??''
            ]
        ));
        $this->routes->add('api_save_webhook', new Route(
            '/api/webhook/save',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::saveWebhook',
                'methods' => ['POST','GET'],
                'webhook' => $this->hashedWebhook??'',
                'company' => $this->company
            ]
        ));

    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }
}
