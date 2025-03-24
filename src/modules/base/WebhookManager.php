<?php

namespace NS2B\SDK\MODULES\BASE;

use NS2B\SDK\DATABASE\DatabaseSQLite as Db;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;

use DOMDocument;
use stdClass;

class WebhookManager extends ServiceBuilderFactory
{
    
    private $webhook;
    private const CIPHER_METHOD = 'aes-256-cbc';
    private const KEY_LENGTH = 32; // 256 bits
    private const IV_LENGTH = 16;  // 128 bits
    

    public function __construct(
        private Db $databases,
        private stdClass $webhookCollection=new stdClass()
    ) {  
        $this->webhookCollection->entity = 'webhooks';
        $this->webhookCollection->database = $databases;
        $this->webhookCollection->fields = [
            'WEBHOOK'=>'TEXT NOT NULL',
            'CREATED_AT'=>'DATETIME DEFAULT CURRENT_TIMESTAMP'
        ];
        $this->webhookCollection->database->createEntity($this->webhookCollection->entity, $this->webhookCollection->fields);
        $this->webhookCollection->template = __DIR__ . '/404.php';
        $this->webhookCollection->includes = [];
        $this->webhookCollection->requiredScopes = ['crm'];
        $this->webhookCollection->safeRoutes = [
            '/webhook',
            '/api/webhook/save',
            '/api/webhook'
        ];
        $this->webhookCollection->B24 = null;
        $this->webhookCollection->hasScope = false;
        $this->webhookCollection->request = Request::createFromGlobals();
        $this->firewall();
    }



    public function firewall(string  $redirecToRoute=null): Response|self{
        $this->addSafeRoutes([$redirecToRoute]);
        $server=$this->webhookCollection->request->server;  
       
        switch(true){
            case !in_array($server->get("PATH_INFO"),$this->webhookCollection->safeRoutes) && !$this->hasScope():
                if($redirecToRoute){
                   echo  '<div style="display:none;">'.new RedirectResponse('//'.$server->get("HTTP_HOST").$server->get("SCRIPT_NAME").$redirecToRoute,302).'</div>';
                   exit;
                }
                echo '<div style="display:none;">'.new RedirectResponse('//'.$server->get("HTTP_HOST").$server->get("SCRIPT_NAME").$this->webhookCollection->safeRoutes[0],302).'</div>';
                exit;
                break;
            default:
                break;
        }
        return $this;
    }

    public function getSafeRoutes(): array{
        return $this->webhookCollection->safeRoutes;
    }

    public function requiredScopes(array $scopes): self{
        $this->webhookCollection->requiredScopes=$scopes;
        return $this;
    }

    public function setB24(){
        if(!empty($_SERVER['DOCUMENT_ROOT'])&& file_exists($file=$_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php"))
        include_once($file);
        
        switch(true){
            case (!empty($_REQUEST['APP_SID']) && !empty($_REQUEST['APP_SECRET'])):
                $appProfile = ApplicationProfile::initFromArray([
                    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => $_REQUEST['APP_SID'],
                    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => $_REQUEST['APP_SECRET'],
                    'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => $this->webhookCollection->requiredScopes
                ]);
                break;
        
            default:
                if($webhook=$this->getDecrypted()){

                    $this->webhookCollection->B24 = self::createServiceBuilderFromWebhook(
                        $webhook//'https://bitrix24demoec.ns2b.fr/rest/12/2neihcmydm0tpxux/'
                    );
                    
                }
                
                break;
        }
        
        return $this;
    }

    public function B24(){
        $this->setB24();
        return $this->webhookCollection->B24;
    }

    public function currentScope(): self{
        $this->webhookCollection->currentScope = $this->webhookCollection->B24?->core->call('scope')->getResponseData()->getResult()??[];
        return $this;
    }

    public function missingScopes(): self{
        foreach($this->webhookCollection->requiredScopes as $requiredScope){
            if(!empty($this->webhookCollection->currentScope) && !in_array($requiredScope,$this->webhookCollection->currentScope)){
                $this->webhookCollection->missingScopes[] = $requiredScope;
            }
        }
        return $this;
    }

    public function hasScope(){
        $this->setB24()->currentScope()->missingScopes();
        return $this->webhookCollection->hasScope=empty($this->webhookCollection->missingScopes) && !empty($this->webhookCollection->currentScope)?true:false;
       
    }

      /**
     * Définit le template principal
     */
    public function setTemplate(?string $template): self
    {
        $this->webhookCollection->template = $template;
        return $this;
    }

 


    public function getCollection(){
        return $this->webhookCollection;
    }


     /**
     * Récupère le dernier webhook enregistré.
     */
    private function getDecrypted(): ?string
    {   try{
            $this->hasWebhookTable();
            $result = $this->webhookCollection->database->selects($this->webhookCollection->entity);
            
            $this->webhook = $result[0]['WEBHOOK']??NULL;
            if (
               ! $this->webhookCollection->isValidWebhook=$this->isValidWebhook($this->webhook=$this->decryptWebhook($this->webhook))) 
            {
                throw new \InvalidArgumentException("Le webhook est invalide: $this->webhook");
            }
        } catch (\InvalidArgumentException $e) {
            $this->log($e,$e->getMessage());
        }
       return $this->webhook;
    }

    public function hasWebhookTable(): bool{
        $database=$this->webhookCollection->database;
        if(!$database?->dbExists()){
            $database?->createDatabase($database->getDatabaseName());
            if(!$database?->entityExists($this->webhookCollection->entity)){
                return $database?->createEntity($this->webhookCollection->entity);
            }
        }else if(!$database?->entityExists($this->webhookCollection->entity)){
            return $database?->createEntity($this->webhookCollection->entity);
        }
        if($database?->entityExists($this->webhookCollection->entity))
            return true;
        
        return false;
    }

    

    /**
     * Récupère le dernier webhook enregistré.
     */
    
    public function getWebhook(): string
    {   
        if ($this->getDecrypted()) {
            $url=parse_url($this->webhook);
            $this->webhookCollection->webhook= $url['scheme'] . '://' . $url['host'] . preg_replace('/[0-9a-z]/', '*', $url['path']??''); 
        }
        return $this->webhookCollection->webhook??'';
    }

    private function isValidWebhook(string $webhook): bool
    {   
        $url = parse_url($webhook);
        if ($url === false) {
            return false;
        }
        if (!isset($url['scheme'], $url['host'], $url['path']) || !str_contains($url["host"],'.')) {
            return false;
        }
        $path = explode('/', $url['path']);
        if (count($path) !== 5) {
            return false;
        }
        if ($path[1] !== 'rest' || !is_numeric($path[2])) {
            return false;
        }
        return true;
    }


  
    /**
     * Ajoute du contenu HTML pour un ID spécifique
     */
    protected function addInclude(string $targetId, string $html): self
    {
        $this->webhookCollection->includes[$targetId] = $html;
        return $this;
    }
    public function addSafeRoutes(array $routes): self{
        $this->webhookCollection->safeRoutes =  array_merge($this->webhookCollection->safeRoutes, $routes);
        return $this;
    }



    /**
     * Génère et stocke le HTML à partir d'un template avec des variables
     */
    public function processTemplate(string $template, array $arResult): self
    {
        if (!isset($arResult['attribut_id'])) {
            throw new \InvalidArgumentException("La clé 'attribut_id' est requise dans arResult");
        }

        $html = $this->generateHtml($template, $arResult);
        $this->addInclude($arResult['attribut_id'], $html);
        
        return $this;
    }

    /**
     * Génère le HTML à partir d'un template et de variables
     */
    protected function generateHtml(string $template, array $arResult = [], array $contents= []): string
    {
        if (!file_exists($template)) {
            throw new \RuntimeException("Le template '$template' n'existe pas");
        }

        ob_start();
        extract($arResult);
        include $template;
        return ob_get_clean();
    }

    private function log($e,$message){
        if (defined('DEBUG') && DEBUG){
            echo'<pre>';
            var_dump($e);
            echo'</pre>';
            error_log($message . ': ' . $e->getMessage(), 0, __DIR__ . '/error.log');
            exit;
       }
    }

    

    public function render(){
        return $this->generateHtml($this->webhookCollection->template, contents:$this->webhookCollection->includes);
    }


    


    /**
     * Récupère le webhook au format JSON pour les requêtes AJAX.
     */
    protected function ajaxGetWebhook(Request $request): JsonResponse
    {
        if ($request->isMethod('GET')) {
            $result = $this->getWebhook();
            return new JsonResponse([
                'success' => true,
                'webhook' => $result
            ], Response::HTTP_OK);
        }
        
        return new JsonResponse([
            'success' => false,
            'error' => 'Méthode non autorisée'
        ], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Supprime tous les webhooks.
     */
    public function deleteWebhook(): bool
    { 
        if($this->hasWebhookTable()){
            $this->webhookCollection->database->deleteAll($this->webhookCollection->entity);
            if(!$this->getWebhook()){
                return true;
            }
        } 
        return false;
    }

    /**
     * Gère les différentes actions liées aux webhooks.
     */
    public function askWebhook(): mixed
    {   
        $request = $this->webhookCollection->request;
        switch ($request->query->get('action')) {
            case 'savewebhook':
                $this->save($request);
                break;
                
            case 'getwebhook':
                return$this->ajaxGetWebhook($request);
                break;

            case 'displaywebhook':
                $this->renderWebhookPopup();
                break;

            default:
                if (!$this->getWebhook()) {
                    $this->renderWebhookPopup();
                }
                break;
        }

        return $this;
    }

    public function show(){
        $this->renderWebhookPopup();
        return $this->render();
    }

        /**
     * Sauvegarde un webhook dans la base de données.
     */
    public function save(Request $request): array
    {   
        
        if ($request->isMethod('POST') && $this->hasWebhookTable()) {
            $data = json_decode($request->getContent(), true);
            $webhook = filter_var($data["data"]['webhook'] ?? '', FILTER_VALIDATE_URL);
            
            if ($webhook && $this->isValidWebhook($webhook)) {
                $message = [
                    'status' => 'success',
                    'message' => 'Webhook '.$webhook.'received successfully '
                ];
                $this->webhookCollection->database->deleteAll($this->webhookCollection->entity);
                if($this->webhookCollection->database->insert(
                    $this->webhookCollection->entity, 
                    [
                    'WEBHOOK' => $this->encryptWebhook($webhook)
                    ]
                )){
                    $message = [
                        'status' => 'success',
                        'message' => 'Webhook '.$webhook.'saved successfully '
                    ];
                }
                
            }else{
                $message = [
                    'status' => 'error',
                    'message' => 'Webhook not valid : '.$data["data"]['webhook']
                ];
            }
        }else{
            $message = [
                'status' => 'error',
                'message' => 'Method not allowed'
            ];
        }

        return $message;
    }

    

    /**
     * Affiche la page avec la popup de webhook
     */
    public function renderWebhookPopup($arResult=[]): self
    {
        $this->processTemplate(__DIR__ . '/snippets/popupTemplate.php', [
            'attribut_id' => 'main-content',
            'webhookUrl' => $this->getWebhook()??'',
            'arResult' => $arResult
        ]);
        return $this;
    }

    /**
     * Affiche la page d'accueil
     */
    public function renderHome(): self
    {
        $this->webhookCollection->template = dirname(__DIR__, 2) . '/modules/crm.company.insee/templates/templateblank.php';
        return $this;
    }

    /**
     * Crypte une URL de webhook
     * @param string $webhook L'URL du webhook à crypter
     * @return string Le webhook crypté en base64
     */
    public function encryptWebhook(string $webhook): string 
    {
        // Générer une clé sécurisée et un vecteur d'initialisation
        $key = openssl_random_pseudo_bytes(self::KEY_LENGTH);
        $iv = openssl_random_pseudo_bytes(self::IV_LENGTH);
        
        // Crypter le webhook
        $encrypted = openssl_encrypt(
            $webhook,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($encrypted === false) {
            throw new \RuntimeException("Erreur lors du cryptage du webhook");
        }
        
        // Combiner IV + clé + données cryptées
        $combined = $iv . $key . $encrypted;
        
        // Encoder en base64 pour stockage sécurisé
        return base64_encode($combined);
    }

    /**
     * Décrypte une URL de webhook
     * @param string $encryptedWebhook Le webhook crypté en base64
     * @return string L'URL du webhook décryptée
     */
    public function decryptWebhook($encryptedWebhook): string {
        if(empty($encryptedWebhook)||!is_string($encryptedWebhook)){
            return '';
        }
        $database = $this->webhookCollection->database;
        // Décoder le webhook crypté
        $combined = base64_decode($encryptedWebhook);
        
        if ($combined === false) {
            throw new \RuntimeException("Données de webhook invalides");
        }
        
        // Extraire IV, clé et données cryptées
        $iv = substr($combined, 0, self::IV_LENGTH);
        $key = substr($combined, self::IV_LENGTH, self::KEY_LENGTH);
        $encrypted = substr($combined, self::IV_LENGTH + self::KEY_LENGTH);
        
        // Décrypter le webhook
        $decrypted = openssl_decrypt(
            $encrypted,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($decrypted === false) {
            $database->deleteAll($this->webhookCollection->entity);
            throw new \RuntimeException("Erreur lors du décryptage du webhook");
        }
        
        return $decrypted;
    }

}
