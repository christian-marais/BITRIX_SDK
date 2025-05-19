<?php
namespace NS2B\SDK\MODULES\CRM\COMPANY\STORAGE\ROUTES\API;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

use NS2B\SDK\MODULES\CRM\COMPANY\STORAGE\CompanyComponent;
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
        $this->defineRoutes();
    }

    private function defineRoutes():void
    {
        // Route api pour récupérer le webhook
        $this->routes->add('api_get_webhook', new Route(
            '/api/webhook',
            [
                '_controller' => 'NS2B\SDK\MODULES\CRM\COMPANY\STORAGE\ROUTES\API\ApiController::getWebhook',
                'methods' => ['POST'],
                'webhook' => $this->hashedWebhook??'',
                'company' => $this->company,
                'B24' => $this->B24
            ]
        ));
    }
   
    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    
}
