<?php
namespace NS2B\SDK\MODULES\CRM\COMPANY\INSEE\API;

use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\CompanyComponent;
use NS2B\SDK\MODULES\BASE\WebhookManager;

class ApiRoutes {
    private $company;
    private $webhookManager;

    public function __construct() {
        $this->company = new CompanyComponent();
        $this->webhookManager = new WebhookManager();
    }

    public function handleRequest() {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        header('Content-Type: application/json');

        switch (true) {
            case preg_match('/^\/api\/company\/check$/', $uri) && $method === 'POST':
                return $this->checkCompany();

            case preg_match('/^\/api\/company\/add$/', $uri) && $method === 'POST':
                return $this->addCompany();

            case preg_match('/^\/api\/company\/save-to-db$/', $uri) && $method === 'POST':
                return $this->saveCompanyToDb();

            case preg_match('/^\/api\/webhook\/save$/', $uri) && $method === 'POST':
                return $this->saveWebhook();

            default:
                http_response_code(404);
                return json_encode(['error' => 'Route not found']);
        }
    }

    private function checkCompany() {
        $siret = $_POST['siret'] ?? null;
        if (!$siret) {
            http_response_code(400);
            return json_encode(['error' => 'SIRET is required']);
        }

        $company = $this->company->getCompanyWithSiretFromBitrix($siret);
        $exists = isset($company->companyCollection->currentCompany);

        return json_encode([
            'exists' => $exists,
            'url' => $exists ? "/company/{$company->companyCollection->currentCompany['ID']}" : null,
            'id' => $exists ? $company->companyCollection->currentCompany['ID'] : null
        ]);
    }

    private function addCompany() {
        $siret = $_POST['siret'] ?? null;
        $siren = $_POST['siren'] ?? null;
        $parentId = $_POST['parentId'] ?? null;

        if (!$siret || !$siren) {
            http_response_code(400);
            return json_encode(['error' => 'SIRET and SIREN are required']);
        }

        $fields = [
            $this->company->fields['siret'] => $siret,
            $this->company->fields['siren'] => $siren
        ];

        if ($parentId) {
            $fields['PARENT_ID'] = $parentId;
        }

        $result = $this->company->addCompanyToBitrix($fields);
        
        return json_encode([
            'success' => true,
            'url' => "/company/{$result->companyCollection->currentCompany['ID']}"
        ]);
    }

    private function saveCompanyToDb() {
        $siret = $_POST['siret'] ?? null;
        $siren = $_POST['siren'] ?? null;

        if (!$siret || !$siren) {
            http_response_code(400);
            return json_encode(['error' => 'SIRET and SIREN are required']);
        }

        $tableName = 'companies_' . strtolower(basename(dirname(__DIR__)));
        $this->company->database->createEntity($tableName, [
            'siret' => $siret,
            'siren' => $siren,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return json_encode(['success' => true]);
    }

    private function saveWebhook() {
        $webhook = $_POST['webhook'] ?? null;

        if (!$webhook) {
            http_response_code(400);
            return json_encode(['error' => 'Webhook is required']);
        }

        $this->webhookManager->saveWebhook($webhook);
        return json_encode(['success' => true]);
    }
}

// Initialiser et gérer les routes
$api = new ApiRoutes();
echo $api->handleRequest();
