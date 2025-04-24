<?php
namespace NS2B\SDK\MODULES\BASE;

use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Symfony\Component\HttpFoundation\Request;
use NS2B\SDK\DATABASE\DatabaseSqlite as Database;
use NS2B\SDK\MODULES\BASE\WebhookManager as WebhookManager;

abstract class Base{
   
    protected $action;
    protected $webhookManager;
    protected $entity;
    protected $fields;
    protected $entityFields;
    protected $activityCollection;
    protected $errorMessages;
    protected $database;
    protected $itemsPerPage;
    protected $currentPage;
    protected $contextId;
    protected $currentScope;
    protected $requiredScopes;
    protected $B24;
    private $HttpOption;

    public function __construct($webhook=null) {
        $database = new Database('database');
        $this->webhookManager = new WebhookManager($database);
        $this->webhookManager->requiredScopes($this->requiredScopes);
        $this->B24=$this->webhookManager->B24();
        $this->currentScope=$this->webhookManager->currentScope()->getCollection()->currentScope;
        $this->activityCollection = new \stdClass();
        $this
            ->setAction()
            ->checkRequiredScopes()
            ->setItemsPerPage()
            ->setCurrentPage();
    
    }

    public function getHttpOption(){
        return $this->HttpOption;
    }

    public function setAction(){
        $this->action[]=htmlspecialchars(strip_tags($_GET['action']??''));
        return $this;
    }
    
    public function checkRequiredScopes() {
        if (!is_array($this->requiredScopes)||!is_array($this->currentScope)) return $this;
        $missingScopes = array_diff($this->requiredScopes, $this->currentScope);
        if (!empty($missingScopes)) {
            $this->errorMessages[] = "Scopes manquants : " . implode(', ', $missingScopes);
        }
        return $this;
    }

    // Méthode pour obtenir les scopes manquants
    public function getMissingScopes() {
        if (!is_array($this->requiredScopes)||!is_array($this->currentScope)) return [];
        return array_diff($this->requiredScopes, $this->currentScope);
    }

    public function hasScope(string $scope) {
        if (!is_string($scope)||!is_array($this->currentScope)) return false;
        return $this->B24 && in_array($scope, $this->currentScope);
    }

    public function dd($value){
        $this->log($value);
    }

    public function log($e, $message = 'Erreur lors de la récupération') {
        if (defined('DEBUG') && DEBUG){
            echo'<pre>';
            var_dump($e);
            echo'</pre>';
            error_log($message . ': ' . $e->getMessage(), 0, __DIR__ . '/error.log');
            exit;
       } 
    }

    /**
     * Set items per page
     *
     * @param int $itemsPerPage
     * @return self
     */
    public function setItemsPerPage($itemsPerPage=null) {
        $itemsPerPage = $itemsPerPage??(isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10);
        $this->itemsPerPage = max(1, intval($itemsPerPage));
        return $this;
    }

    /**
     * Get items per page
     *
     * @return int
     */
    public function getItemsPerPage(): int {
        return $this->itemsPerPage??10;
    }

    protected static function getContextId() {
        if (!isset($_REQUEST["PLACEMENT_OPTIONS"]) || empty($_REQUEST["PLACEMENT_OPTIONS"])) {
            return null;
        }
        return (int)htmlentities(json_decode($_REQUEST["PLACEMENT_OPTIONS"])->ID);
    }

    /**
     * Set current page
     *
     * @param int $currentPage
     * @return self
     */
    public function setCurrentPage($currentPage=null) {
        // Gestion des paramètres de pagination

        $currentPage = $currentPage??(isset($_GET['page']) ? intval($_GET['page']) : 1);
        $this->currentPage = max(1, intval($currentPage));
        return $this;
    }

    /**
     * Get current page
     *
     * @return int
     */
    public function getCurrentPage(): int {
        return $this->currentPage;
    }

    /**
     * Notify user
     *
     * @param string $message
     * @return bool
     */
    public function notify(string $message,$user=null){
        $user = 
        (method_exists($USER, 'getContext') && $USER->getContext()?->getUserId())?
        $user??$USER->getContext()?->getUserId():
        $user;
        if(
            !empty($this->B24) &&
            !empty($user) 
        ){
            return $this->B24->core->call('user.notify.personal.add', [
                'USER_ID' => (int) $user,
                'MESSAGE' => (string) $message
            ]);
        }
    }

    /**
     * Send a message in a chat im.recet.list
     * @param string $message
     * @return bool
     */
    public function chat(string $dialogId,string $message){
        if(
            !empty($this->B24) &&
            !empty($USER) &&
            method_exists($USER, 'getContext') &&
            $user=$USER->getContext()?->getUserId()
        ){
            return $this->B24?->core->call('im.message.add', [
                'DIALOG_ID' => (string) $dialogId,
                'MESSAGE' => (string) $message,
                'SYSTEM'=>'N',
                'ATTACH'=>'',
                'URL_PREVIEW'=>'Y',
                "KEYBOARD" => "",
                "MENU" => ""
            ]);
        }
    }



    abstract protected function getCollection();

   
}
