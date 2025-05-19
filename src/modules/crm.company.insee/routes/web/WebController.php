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


    public function uploadCompanyFile(Request $request,...$params): Response
    {
        $this->lightDdosDetect();
        error_log('Starting uploadCompanyFile...');
        try {
            extract($params);
            $fields=$company["fields"]["bitrix"];
            $requestBody=json_decode($request->getContent(), true);
            error_log('Processing uploadCompanyFile...');
            $company=$this->component->getCompanyById([
                    $fields["code"]=> $params["code"],
                    "id"=> $params["id"]
                ]
            );
            switch(true){
                case empty($company):
                    throw new \Exception('Company not found');
                    break;
                case !empty($company[$fields["code"]])&&
                    $company[$fields["code"]]!== $params["code"]:
                    throw new \Exception('Code mismatched');
                    break;
            }
            ob_start();
            include TEMPLATE_DIR.'upload.php';
            $content = ob_get_clean();
            return new Response($content,200);
        } catch (\Exception $e) {
            error_log('Error response uploadCompanyFile...'.$e->getMessage());
            ob_start();
            include TEMPLATE_DIR.'404.php';
            $content = ob_get_clean();
            return new Response($content,404);
        
        }
    }


    private function lightDdosDetect(){
        session_start();

        $ip = $_SERVER['REMOTE_ADDR'];
        $delay = 10; // secondes entre chaque requête autorisée
        $max_requests = 20; // seuil en session (si un bot insiste)

        // Stockage en fichier ou en session (ici simple session pour démo)
        if (!isset($_SESSION['requests'])) {
            $_SESSION['requests'] = [];
        }

        $now = time();

        if (!isset($_SESSION['requests'][$ip])) {
            $_SESSION['requests'][$ip] = ['last_time' => $now, 'count' => 1];
        } else {
            $last_time = $_SESSION['requests'][$ip]['last_time'];
            $count = $_SESSION['requests'][$ip]['count'];

            if ($now - $last_time < $delay) {
                $_SESSION['requests'][$ip]['count']++;

                if ($count >= $max_requests) {
                    $this->ErrorPage("Too many requests. Please try again later.","429");
                } else {
                    $this->ErrorPage("Please wait $delay seconds between requests.","429");
                }
                exit;
            } else {
                // Réinitialise la fenêtre de temps
                $_SESSION['requests'][$ip] = ['last_time' => $now, 'count' => 1];
            }
        }


    }

    private function ErrorPage($message=null,$code=null){
        if(file_exists(dirname(__DIR__,2).'/templates/error.php')) {
            $content=include(dirname(__DIR__,2).'/templates/error.php');
            return new Response($content, 404);
        }
        return new JsonResponse([
            'status' => 'error',
            'message' => $message
        ], $code);
    }

    public function page404(Request $request,...$params): Response
    {   if(file_exists(__DIR__.'/404.php')) {
            return new Response(file_get_contents(__DIR__.'/404.php'), 404);
        }
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Page not found'
        ], 404);
    }

    public function viewBlank(Request $request,...$params): Response
    {  
        // Récupérer les données de l'entreprise
        $company = $params["company"];
        if(!empty($company['SIRET']))
            return new RedirectResponse(BASE_URL.'company/'.$company['SIRET']);

        ob_start();
        // Charger le template blank
        include TEMPLATE_DIR.'templateblank.php';
        $content = ob_get_clean();

        return new Response($content);
    }
    public function home(): Response
    {  
        return new RedirectResponse(
            BASE_URL.'company/', 302);
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

    
    public function saveWebhook(Request $request,...$params): Response
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

    public function getWebhook(Request $request,...$params): Response
    {
        $response = $this->webhookManager->getWebhook();
        return new JsonResponse(
            data:$response,
            status:200
        );
    }

}
