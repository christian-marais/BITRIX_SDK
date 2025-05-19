<?php
namespace NS2B\SDK\MODULES\CRM\COMPANY\STORAGE\ROUTES\API;

use Bitrix24\SDK\Services\ServiceBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use NS2B\SDK\MODULES\BASE\WebhookManager;
use NS2B\SDK\DATABASE\DatabaseSQLite;
use NS2B\SDK\MODULES\CRM\COMPANY\STORAGE\CompanyComponent;
use Symfony\Component\HttpClient\HttpClient;

class ApiController
{   
    private WebhookManager $webhookManager;

    public function __construct(
        private CompanyComponent $companyComponent=new CompanyComponent(),
        private DatabaseSQLite $db=new DatabaseSQLite(),
        private \stdClass $company=new \stdClass()
    ) {
        $this->webhookManager = new WebhookManager($this->db);
    }

    public function getWebhook(Request $request,...$params): Response
    {   
        try {
            if(empty($webhook=$this->webhookManager->getWebhook())) {
                throw new \Exception('Webhook not found');
            }
            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'webhook' => $webhook??''
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $webhook.' '.$e->getMessage()
            ], 404);
        }
    }
}
