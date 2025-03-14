<?php
namespace NS2B\SDK\MODULES\BASE;

use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Symfony\Component\HttpFoundation\Request;
use NS2B\SDK\DATABASE\DatabaseSqlite as Database;

abstract class Base extends ServiceBuilderFactory{
   
    protected $action;
    protected $webhook;
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

    public function __construct($webhook=null) {
        $this->webhook=$webhook??$this->webhook;
        $this->activityCollection = new \stdClass();
        $database = new Database('database');
        $this
            ->setAction()
            ->checkRequiredScopes()
            ->setItemsPerPage()
            ->setCurrentPage();
        
        if(!empty($_SERVER['DOCUMENT_ROOT'])&& file_exists($file=$_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php"))
            include_once($file);
        
        switch(true){
            case (!empty($_REQUEST['APP_SID']) && !empty($_REQUEST['APP_SECRET'])):
                $appProfile = ApplicationProfile::initFromArray([
                    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => $_REQUEST['APP_SID'],
                    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => $_REQUEST['APP_SECRET'],
                    'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => $this->requiredScopes
                ]);
                break;
          
            default:
                $this->B24 = self::createServiceBuilderFromWebhook(
                    $webhook??'https://bitrix24demoec.ns2b.fr/rest/12/2neihcmydm0tpxux/'
                );
                break;
        }

        $this->currentScope = $this->B24?->core?->call('scope')->getResponseData()->getResult();
    }

    

    public function setAction(){
        $this->action[]=htmlspecialchars(strip_tags($_GET['action']??''));
        return $this;
    }
    
    public function checkRequiredScopes() {
        $missingScopes = array_diff($this->requiredScopes, $this->currentScope);
        if (!empty($missingScopes)) {
            $this->errorMessages[] = "Scopes manquants : " . implode(', ', $missingScopes);
        }
        return $this;
    }

    // Méthode pour obtenir les scopes manquants
    public function getMissingScopes() {
        return array_diff($this->requiredScopes, $this->currentScope);
    }

    public function hasScope($scope) {
        return $this->B24 && in_array($scope, $this->currentScope);
    }

    public function dd($value){
        echo'<pre style="color:white;background-color:black;">';
        var_dump($value);
        echo'</pre>';
        die();
    }

    public function log($e, $message = 'Erreur lors de la récupération') {
        echo'<pre>';
        var_dump($e);
        echo'</pre>';
        error_log($message . ': ' . $e->getMessage(), 1, __DIR__ . '/error.log');
    }

    
    public function setItemsPerPage($itemsPerPage=null) {
        $itemsPerPage = $itemsPerPage??(isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10);
        $this->itemsPerPage = max(1, intval($itemsPerPage));
        return $this;
    }

    protected static function getContextId() {
        if (!isset($_REQUEST["PLACEMENT_OPTIONS"]) || empty($_REQUEST["PLACEMENT_OPTIONS"])) {
            return 0;
        }
        return (int)htmlentities(json_decode($_REQUEST["PLACEMENT_OPTIONS"])->ID);
    }

    public function setCurrentPage($currentPage=null) {
        // Gestion des paramètres de pagination

        $currentPage = $currentPage??(isset($_GET['page']) ? intval($_GET['page']) : 1);
        $this->currentPage = max(1, intval($currentPage));
        return $this;
    }

    protected function notify(string $message){
        if(
            !empty($this->B24) &&
            !empty($USER) &&
            method_exists($USER, 'getContext') &&
            $user=$USER->getContext()?->getUserId()
        ){
            return $this->B24->core->call('user.notify.personal.add', [
                'USER_ID' => (int) $user,
                'MESSAGE' => (string) $message
            ]);
        }
    }

    abstract protected function getCollection();

   
}
