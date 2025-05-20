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
        // Route api pour ajouter une entreprise dans bitrix
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

        // Route api pour ajouter une entreprise dans bitrix
        $this->routes->add('api_update_company', new Route(
            'api/company/{id}/{siret}/update',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::updateCompany',
                'methods' => ['GET'],
                'company' => $this->company,
                'B24' => $this->B24,
            ],
            [
                'siret' => '\d{14}',
                'id' => '\d+'
            ]
        ));

         // Route api pour ajouter un contact dans bitrix
         $this->routes->add('api_add_contact', new Route(
            'api/company/contact/save',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::saveContact',
                'methods' => ['POST'],
                'company' => $this->company,
                'B24' => $this->B24,
            ]
        ));

          // Route api pour ajouter un contact dans bitrix
          $this->routes->add('api_upload_file', new Route(
            'api/company/{id}/storage/upload/{code}/',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::uploadCompanyFile',
                'methods' => ['POST'],
                'company' => $this->company,
                'B24' => $this->B24,
            ],
            [
                'id' => '\d+',
                'code' => '[a-z0-9]+'
            ]
        ));

         // Route api pour ajouter un contact dans bitrix
         $this->routes->add('api_get_contacts', new Route(
            'api/company/contacts',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::getContacts',
                'methods' => ['POST','GET'],
                'company' => $this->company,
                'B24' => $this->B24,
            ]
        ));

        // Route api pour récupérer les alertes bodacc dans bitrix
        $this->routes->add('api_bodacc_alerts_company', new Route(
            'api/company/bodacc-alerts',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::bodaccAlertsCompany',
                'methods' => ['GET'],
                'company' => $this->company,
                'B24' => $this->B24,
            ]
        ));

        // Route api pour notifier les alertes bodacc dans bitrix
        $this->routes->add('api_notify_bodacc_alerts_company', new Route(
            'api/company/bodacc-alerts/notify',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::notifyBodaccAlerts',
                'methods' => ['GET'],
                'company' => $this->company,
                'B24' => $this->B24,
            ]
        ));
        
        // Route api pour récupérer une entreprise de bitrix à partir du siret
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

        /**
         * Route api pour récupérer les entreprises de bitrix à partir d'un array de sirets
         * 
         * Body=[{
             *      "method": "crm.company.list",
             *      "name": "87942768000019",
             *     "params": {
             *        "filter[UF_CRM_1713268514492]": "87942768000019"
             *   }
             *},
             *{
             *   "method": "crm.company.list",
             *  "name": "52075381500015",
             * "params": {
             *    "filter[UF_CRM_1713268514492]": "52075381500015"
             *}
             *
        *   }]
        */
        $this->routes->add('api_get_company', new Route(
            'api/companies/siret',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::getCompanies',
                'methods' => ['POST'],
                'company' => $this->company,
                'B24' => $this->B24
            ]
        ));

        $this->routes->add('api_add_user_to_nextcloud', new Route(
            'api/nextcloud/user/add',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::addUserToNextcloud',
                'methods' => ['POST'],
                'company' => $this->company,
                'B24' => $this->B24
            ]
        ));

        $this->routes->add('api_add_folder_to_nextcloud', new Route(
            'api/nextcloud/folder/add',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::addFolderToNextcloud',
                'methods' => ['POST'],
                'company' => $this->company,
                'B24' => $this->B24
            ]
        ));

        $this->routes->add('api_get_user_from_nextcloud', new Route(
            'api/nextcloud/user',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::getUserFromNextcloud',
                'methods' => ['POST'],
                'company' => $this->company,
                'B24' => $this->B24
            ]
        ));
        $this->routes->add('api_share_folder_to_nextcloud', new Route(
            'api/nextcloud/folder/share',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::shareFolderToNextcloud',
                'methods' => ['POST'],
                'company' => $this->company,
                'B24' => $this->B24
            ]
        ));

        $this->routes->add('api_find_folder_to_nextcloud', new Route(
            'api/nextcloud/folder/find',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::findNextcloudFolder',
                'methods' => ['POST'],
                'company' => $this->company,
                'B24' => $this->B24
            ]
        ));

        $this->routes->add('api_create_user_share_space', new Route(
            'api/nextcloud/space/create',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API\ApiController::createUserShareSpace',
                'methods' => ['POST'],
                'company' => $this->company,
                'B24' => $this->B24
            ]
        ));

        //Route api pour récupérer une entreprise de l'annuaire à partir du siret
         
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

        // Route api pour mettre à jour une entreprise à partir du siret
        $this->routes->add('api_siret_update_company', new Route(
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
      

        // Route api pour récupérer le webhook
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
        // Route api pour sauvegarder le webhook
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
