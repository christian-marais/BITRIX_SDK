<?php
namespace NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use NS2B\SDK\MODULES\BASE\WebhookManager;
use NS2B\SDK\DATABASE\DatabaseSQLite;
use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\CompanyComponent;

class ApiController
{   
    private WebhookManager $webhookManager;
    private DatabaseSQLite $db;
    private CompanyComponent $companyComponent;
    private object $company;

    public function __construct()
    {
        $this->db = new DatabaseSQLite();
        $this->companyComponent = new CompanyComponent();
        $this->webhookManager = new WebhookManager($this->db);
        $this->company = new \stdClass();
    }
    public function saveCompany(Request $request,...$params): Response
    {
        try {
            $data = json_decode($request->request->get('data'), true);
            
            // Logique pour ajouter une entreprise
            // TODO: Implémenter la logique d'ajout

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Entreprise ajoutée avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
    public function saveCompanyToDb(Request $request, ...$params): Response
    {   
        

        try {
            $database=new DatabaseSQLite('crmcompanyinsee.db');
            $data = json_decode($request->getContent(), true);
            
            // Logique pour sauvegarder une entreprise dans la base de données
            // TODO: Implémenter la logique de sauvegarde

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Entreprise sauvegardée avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getCompany(Request $request, ...$params): Response
    {
        try {
            extract($params);
            $this->company->currentCompany["bitrix"] = $this->companyComponent->getCompanyWithSiretFromBitrix($siret)->getCollection();
            if(!$this->company->currentCompany["bitrix"]){
                throw new \Exception('Entreprise introuvable');
            }
                $this->company->currentCompany['exists'] = true;
                $this->company->currentCompany["siret"] = $siret;
                return new JsonResponse([
                    'status' => 'success',
                    'data' => $this->company->currentCompany
                ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function updateCompany(Request $request, ...$params): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Logique pour mettre à jour une entreprise
            // TODO: Implémenter la logique de mise à jour

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Entreprise mise à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function saveWebhook(Request $request,...$params): Response
    {   
       
       try {
            $hasBeenSaved=$this->webhookManager->save($request);
            if(!$hasBeenSaved['status'] == 'success') {
                throw new \Exception($hasBeenSaved['message']);
            }else{
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Webhook sauvegardé avec succès.'.$hasBeenSaved['message']
                ], 200);
            }

       } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getWebhook(Request $request,...$params): Response
    {   
        try {
            if(!isset($params['webhook'])) {
                throw new \Exception('Webhook not found');
            }
            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'webhook' => $params['webhook']??''
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
