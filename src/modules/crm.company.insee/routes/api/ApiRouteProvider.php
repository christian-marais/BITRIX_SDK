<?php
namespace NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\CompanyComponent;
use NS2B\SDK\DATABASE\DatabaseSQLite;
use NS2B\SDK\MODULES\BASE\WebhookManager;

class ApiRouteProvider
{
    private $baseRoute = '/src/modules/crm.company.insee/index.php/';
    private $webhookManager;
    private $company;
    private $hashedWebhook;
    private $B24;

    public function __construct(
        private RouteCollection $routes = new RouteCollection(),
        private CompanyComponent $component = new CompanyComponent(),
        private DatabaseSQLite $db = new DatabaseSQLite()
    ) {
        $this->webhookManager = new WebhookManager($this->db);
        $this->B24=$this->webhookManager->B24();
        $this->hashedWebhook = $this->webhookManager->getWebhook();
        $this->populateCompany();
        $this->defineRoutes();
    }

    private function populateCompany():void{
        $this->company = $this->component
        ->getCompanyFromAnnuaire()
        ->getCompanyFromInsee()
        ->getCompanyFromBodacc()
        ->getBodaccAlerts()
        ->getCompany()
        ;
    }

    private function defineRoutes():void
    {
        // Route pour ajouter une entreprise
        $this->routes->add('api_add_company', new Route(
            'api/company/{siret}/save',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::saveCompany',
                'methods' => ['GET'],
                'company' => $this->company,
                'B24' => $this->B24,
            ],
            [
                'siret' => '\d{14}'
            ]
        ));



        // Route pour récupérer une entreprise
        $this->routes->add('api_get_company', new Route(
            'api/company/{siret}',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::getCompany',
                'methods' => ['GET'],
                'company' => $this->company,
                'B24' => $this->B24
            ],
            [
                'siret' => '\d{14}'
            ]
        ));

        $this->routes->add('api_get_company', new Route(
            'api/companies/siret',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::getCompanies',
                'methods' => ['POST'],
                'company' => $this->company,
                'B24' => $this->B24
            ]
        ));

        $this->routes->add('api_get_annuaire', new Route(
            'api/annuaire/{siret}',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::getAnnuaire',
                'methods' => ['GET'],
                'company' => $this->company,
                'B24' => $this->B24
            ],
            [
                'siret' => '\d{14}'
            ]
        ));

        // Route pour mettre à jour une entreprise
        $this->routes->add('api_update_company', new Route(
          'api/company/{siret}/update',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::updateCompany',
                'methods' => ['PUT'],
                'company' => $this->company,
                'B24' => $this->B24
            ],
            [
                'siret' => '\d{14}'
            ]
        ));
      

        // Route pour récupérer le webhook
        $this->routes->add('api_get_webhook', new Route(
            '/api/webhook',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::getWebhook',
                'methods' => ['POST'],
                'webhook' => $this->hashedWebhook??'',
                'company' => $this->company,
                'B24' => $this->B24
            ]
        ));
        $this->routes->add('api_save_webhook', new Route(
            '/api/webhook/save',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::saveWebhook',
                'methods' => ['PUT'],
                'webhook' => $this->hashedWebhook??'',
                'company' => $this->company,
                'B24' => $this->B24
            ]
        ));

        // Route pour supprimer le webhook
        $this->routes->add('api_delete_webhook', new Route(
            '/api/webhook/delete',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::deleteWebhook',
                'methods' => ['DELETE','PUT'],
                'webhook' => $this->hashedWebhook??''
            ]
        ));

        

    }
   
    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    
}
