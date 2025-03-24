<?php
namespace NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\WEB;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\CompanyComponent;
use NS2B\SDK\MODULES\BASE\WebhookManager;
use NS2B\SDK\DATABASE\DatabaseSQLite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebController
{
    private $component;
    private $webhookManager;
    private $db;

    public function __construct()
    {
        $this->component = new CompanyComponent();
        $this->db = new DatabaseSQLite();
        $this->webhookManager = new WebhookManager($this->db);
        
    }

    public function viewCompany(Request $request,...$params): Response
    {
        
        // Récupérer les données de l'entreprise
        $company=!empty($params["siret"])? 
        $this->component
            ->setCustomSiret($params["siret"])
            ->getCompanyWithSiretFromBitrix()
            ->getCompanyRequisite()
            ->getCompanyFromAnnuaire()
            ->getCompanyFromInsee()
            ->getCompanyFromBodacc()
            ->setCompanySourcesUrl()
            ->getBodaccAlerts()
            ->getCompany():
        $params["company"];
        // Charger le template de présentation
        if(!empty($company["SIRET"])) {
            ob_start();
            include TEMPLATE_DIR.'companyPresentation.php';
            $content = ob_get_clean();
            return new Response($content);
        }else{
          return new RedirectResponse(BASE_URL.'company/');
        }
       
    }

    public function viewBlank(Request $request,...$params): Response
    {  
        // Récupérer les données de l'entreprise
        $company = $params["company"];

        ob_start();
        // Charger le template blank
        include TEMPLATE_DIR.'templateblank.php';
        $content = ob_get_clean();

        return new Response($content);
    }

    public function webhook(Request $request,...$params): Response
    { 
        // Récupérer les données de l'entreprise
        $company = $params["company"];
        $content=$this->webhookManager
            ->renderHome()
            ->show();
        return new Response($content,200);
    }

    
    public function saveWebhook(Request $request): Response
    {
        $success = $this->webhookManager->save($request);

        switch(true){
            case !$request->isMethod('POST'):
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Méthode non autorisée'
                ], Response::HTTP_METHOD_NOT_ALLOWED);
                break;
            case $success:
                return new JsonResponse([
                    'success' => true,
                ], Response::HTTP_OK);
                break;
            default:
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Erreur inconnue'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getWebhook(): Response
    {
        $response = $this->webhookManager->getWebhook();
        return new JsonResponse(
            data:$response,
            status:200
        );
    }


  


}
