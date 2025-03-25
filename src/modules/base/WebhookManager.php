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
        _error_log("starting firewall");
        $this->addSafeRoutes([$redirecToRoute]);
        $server=$this->webhookCollection->request->server;  
        switch(true){
            case !in_array($server->get("PATH_INFO"),$this->webhookCollection->safeRoutes) && !$this->hasScope():
                if($redirecToRoute){
                    _error_log("processing firewall... redirecting to specified route : $redirecToRoute");
                   echo  '<div style="display:none;">'.new RedirectResponse('//'.$server->get("HTTP_HOST").$server->get("SCRIPT_NAME").$redirecToRoute,302).'</div>';
                   exit;
                }
                _error_log('processing firewall... redirecting to default first safe route : '.$this->webhookCollection->safeRoutes[0]); 
                echo '<div style="display:none;">'.new RedirectResponse('//'.$server->get("HTTP_HOST").$server->get("SCRIPT_NAME").$this->webhookCollection->safeRoutes[0],302).'</div>';
                exit;
            case $server->get('REQUEST_URI') ==="/src/modules/crm.company.insee/index.php":
                echo  '<div style="display:none;">'.new RedirectResponse('//'.$server->get("HTTP_HOST").$server->get("SCRIPT_NAME").$this->webhookCollection->safeRoutes[0],302).'</div>';
                exit;
            default:
                break;
        }
        _error_log("ending firewall checks for routing...");
        return $this;
    }

    public function getSafeRoutes(): array{
        _error_log("Processing getSafeRoutes");
        return $this->webhookCollection->safeRoutes;
    }

    public function requiredScopes(array $scopes): self{
        _error_log("Processing requiredScopes");
        $this->webhookCollection->requiredScopes=$scopes;
        return $this;
    }

    public function setB24(){
        _error_log("Starting setB24");
        if(!empty($_SERVER['DOCUMENT_ROOT'])&& file_exists($file=$_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php"))
        include_once($file);
        
        switch(true){
            case (!empty($_REQUEST['APP_SID']) && !empty($_REQUEST['APP_SECRET'])):
                _error_log("Processing setB24... APP_SID: ".$_REQUEST['APP_SID']??'');
                $appProfile = ApplicationProfile::initFromArray([
                    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => $_REQUEST['APP_SID'],
                    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => $_REQUEST['APP_SECRET'],
                    'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => $this->webhookCollection->requiredScopes
                ]);
                break;
        
            default:
                _error_log("Processing setB24... Default...getting decrypted webhook");
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
        _error_log("Processing B24");
        $this->setB24();
        return $this->webhookCollection->B24;
    }

    public function currentScope(): self{
        _error_log("Processing currentScope");
        $this->webhookCollection->currentScope = $this->webhookCollection->B24?->core->call('scope')->getResponseData()->getResult()??[];
        return $this;
    }

    public function missingScopes(): self{
        _error_log("Processing missingScopes");
        foreach($this->webhookCollection->requiredScopes as $requiredScope){
            if(!empty($this->webhookCollection->currentScope) && !in_array($requiredScope,$this->webhookCollection->currentScope)){
                $this->webhookCollection->missingScopes[] = $requiredScope;
            }
        }
        return $this;
    }

    public function hasScope(){
        _error_log("Processing hasScope");
        $this->setB24()->currentScope()->missingScopes();
        return $this->webhookCollection->hasScope=empty($this->webhookCollection->missingScopes) && !empty($this->webhookCollection->currentScope)?true:false;
       
    }

      /**
     * Définit le template principal
     */
    public function setTemplate(?string $template): self
    {
        _error_log("Processing setTemplate");
        $this->webhookCollection->template = $template;
        return $this;
    }

 


    public function getCollection(){
        _error_log("Processing getCollection");
        return $this->webhookCollection;
    }


     /**
     * Récupère le dernier webhook enregistré.
     */
    private function getDecrypted(): ?string
    {   
        _error_log("Starting getDecrypted");
        try{
            _error_log("Starting hasWebhookTable");
            $this->hasWebhookTable();

            $result = $this->webhookCollection->database->selects($this->webhookCollection->entity);
            
            $this->webhook = $result[0]['WEBHOOK']??NULL;
            if (
               ! $this->webhookCollection->isValidWebhook=$this->isValidWebhook($this->webhook=$this->decryptWebhook($this->webhook))) 
            {
                _error_log("Le webhook est invalide: ".$this->webhook);
                throw new \InvalidArgumentException("Le webhook est invalide: $this->webhook");
            }
        } catch (\InvalidArgumentException $e) {
            _error_log($e->getMessage());
            $this->log($e,$e->getMessage());
        }
       return $this->webhook;
    }

    public function hasWebhookTable(): bool{
        _error_log("Processing hasWebhookTable");
        $database=$this->webhookCollection->database;
        if(!$database?->dbExists()){
            _error_log("Creating database: {$database?->getDatabaseName()}");
            $database?->createDatabase($database->getDatabaseName());
            if(!$database?->entityExists($this->webhookCollection->entity)){
                _error_log("Creating entity: {$this->webhookCollection->entity}");
                return $database?->createEntity($this->webhookCollection->entity);
            }
        }else if(!$database?->entityExists($this->webhookCollection->entity)){
            _error_log("Creating entity: {$this->webhookCollection->entity}");
            return $database?->createEntity($this->webhookCollection->entity);
        }
        if($database?->entityExists($this->webhookCollection->entity)){
            _error_log("Entity exists: {$this->webhookCollection->entity}");
            return true;
        }
        _error_log("Entity does not exist: {$this->webhookCollection->entity}");
        return false;
    }

    

    /**
     * Récupère le dernier webhook enregistré.
     */
    
    public function getWebhook(): string
    {   
        _error_log("Starting getWebhook");
        if ($this->getDecrypted()) {
            _error_log("Decrypted webhook: $this->webhook");
            $url=parse_url($this->webhook);
            _error_log("Returning webhook: {$this->webhookCollection->webhook}");
            $this->webhookCollection->webhook= $url['scheme'] . '://' . $url['host'] . preg_replace('/[0-9a-z]/', '*', $url['path']??''); 
        }
        _error_log("No webhook found, returning empty");
        return $this->webhookCollection->webhook??'';
    }

    private function isValidWebhook(string $webhook): bool
    {   
        _error_log("Starting isValidWebhook");
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
        _error_log('Valid webhook: '.substr($webhook, 0, 5).'...');
        return true;
    }


  
    /**
     * Ajoute du contenu HTML pour un ID spécifique
     */
    protected function addInclude(string $targetId, string $html): self
    {   
        _error_log("Starting addInclude ...targetId: $targetId");
        $this->webhookCollection->includes[$targetId] = $html;
        return $this;
    }
    public function addSafeRoutes(array $routes): self{
        _error_log("Starting addSafeRoutes ...routes");
        $this->webhookCollection->safeRoutes =  array_merge($this->webhookCollection->safeRoutes, $routes);
        return $this;
    }



    /**
     * Génère et stocke le HTML à partir d'un template avec des variables
     */
    public function processTemplate(string $template, array $arResult): self
    {
        _error_log("Starting processTemplate ...template: $template");
        if (!isset($arResult['attribut_id'])) {
            _error_log("La clé 'attribut_id' est requise dans arResult");
            throw new \InvalidArgumentException("La clé 'attribut_id' est requise dans arResult");
        }
        _error_log("Adding template...$template");
        $html = $this->generateHtml($template, $arResult);
        _error_log("Added include ...attribut_id: {$arResult['attribut_id']}");
        $this->addInclude($arResult['attribut_id'], $html);
        _error_log("Processed template ...attribut_id: {$arResult['attribut_id']}");
        return $this;
    }

    /**
     * Génère le HTML à partir d'un template et de variables
     */
    protected function generateHtml(string $template, array $arResult = [], array $contents= []): string
    {
        _error_log("Starting generateHtml ...template: $template");
        if (!file_exists($template)) {
            _error_log("Le template '$template' n'existe pas");
            throw new \RuntimeException("Le template '$template' n'existe pas");
        }
        _error_log("Adding template...$template");
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
        _error_log("Starting render...");
        return $this->generateHtml($this->webhookCollection->template, contents:$this->webhookCollection->includes);
    }


    


    /**
     * Récupère le webhook au format JSON pour les requêtes AJAX.
     */
    protected function ajaxGetWebhook(Request $request): JsonResponse
    {
        _error_log("Starting ajaxGetWebhook...");
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
        _error_log("Starting deleteWebhook...");
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
        _error_log("Starting askWebhook...");
        $request = $this->webhookCollection->request;
        switch ($request->query->get('action')) {
            case 'savewebhook':
                _error_log("Saving webhook...");
                $this->save($request);
                break;
                
            case 'getwebhook':
                _error_log("Getting webhook...");
                return$this->ajaxGetWebhook($request);
                break;

            case 'displaywebhook':
                _error_log("Displaying webhook popup...");
                break;

            default:
                _error_log("Default action...");
                if (!$this->getWebhook()) {
                    _error_log("Rendering webhook popup...");
                    $this->renderWebhookPopup();
                }
                break;
        }
        _error_log("Ending askWebhook...");
        return $this;
    }

    public function show(){
        _error_log("Showing webhook popup...");
        $this->renderWebhookPopup();
        return $this->render();
    }

        /**
     * Sauvegarde un webhook dans la base de données.
     */
    public function save(Request $request): array
    {   
        
        if ($request->isMethod('POST') && $this->hasWebhookTable()) {
            _error_log("Validating webhook...");
            $data = json_decode($request->getContent(), true);
            $webhook = filter_var($data["data"]['webhook'] ?? '', FILTER_VALIDATE_URL);
            
            if ($webhook && $this->isValidWebhook($webhook)) {
                _error_log("Webhook is valid...");
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
                    _error_log("Webhook saved successfully...");
                    $message = [
                        'status' => 'success',
                        'message' => 'Webhook '.$webhook.'saved successfully '
                    ];
                }
                
            }else{
                _error_log("Webhook not valid : ".$data["data"]['webhook']);
                $message = [
                    'status' => 'error',
                    'message' => 'Webhook not valid : '.$data["data"]['webhook']
                ];
            }
        }else{
            _error_log("Method not allowed");
            $message = [
                'status' => 'error',
                'message' => 'Method not allowed'
            ];
        }
        _error_log("Ending save...");
        return $message;
    }

    

    /**
     * Affiche la page avec la popup de webhook
     */
    public function renderWebhookPopup($arResult=[]): self
    {
        _error_log("Rendering webhook popup...");
        $this->processTemplate(__DIR__ . '/snippets/popupTemplate.php', [
            'attribut_id' => 'main-content',
            'webhookUrl' => $this->getWebhook()??'',
            'arResult' => $arResult
        ]);
        _error_log("Ending rendering...");
        return $this;
    }

    /**
     * Affiche la page d'accueil
     */
    public function renderHome(): self
    {
        _error_log("Rendering home...");
        $this->webhookCollection->template = dirname(__DIR__, 2) . '/modules/crm.company.insee/templates/templateblank.php';
        _error_log("Template rendered...");
        return $this;
    }
    /**
     * Crypte une URL de webhook
     * @param string $webhook L'URL du webhook à crypter
     * @return string Le webhook crypté en base64
     */
    public function encryptWebhook(string $webhook): string 
    {
        _error_log("Encrypting webhook...");
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
        _error_log("Ending encrypting...");
        return base64_encode($combined);
    }

    /**
     * Décrypte une URL de webhook
     * @param string $encryptedWebhook Le webhook crypté en base64
     * @return string L'URL du webhook décryptée
     */
    public function decryptWebhook($encryptedWebhook): string {
        _error_log("Decrypting webhook...");
        if(empty($encryptedWebhook)||!is_string($encryptedWebhook)){
            _error_log("Empty or invalid encrypted webhook");
            return '';
        }
        $database = $this->webhookCollection->database;
        // Décoder le webhook crypté
        $combined = base64_decode($encryptedWebhook);
        
        if ($combined === false) {
            _error_log("Invalid combined");
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
            _error_log("Invalid decrypted...deleting webhook entity...");
            $database->deleteAll($this->webhookCollection->entity);
            throw new \RuntimeException("Erreur lors du décryptage du webhook");
        }
        _error_log("Ending decrypting...");
        return $decrypted;
    }

}
