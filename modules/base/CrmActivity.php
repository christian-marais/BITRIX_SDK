<?php
namespace NS2B;

use Bitrix24\SDK\Services\ServiceBuilderFactory;

abstract class CrmActivity {
    protected $B24 = null;
    protected $activityCollection = null;
    protected $itemsPerPage = 10;
    protected $currentPage = 1;
    protected $currentScope = [];
    protected $errorMessages = [];
    protected $requiredScopes = [
        'crm',
        'user'
    ];

    public function __construct() {
        $this->activityCollection = new \stdClass();

        // Initialiser la pagination depuis les paramètres GET
        if (isset($_GET['itemsPerPage'])) {
            $this->setItemsPerPage($_GET['itemsPerPage']);
        }
        if (isset($_GET['page'])) {
            $this->setCurrentPage($_GET['page']);
        }

        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
        $this->B24 = ServiceBuilderFactory::createServiceBuilderFromWebhook(
            'https://bitrix24demoec.ns2b.fr/rest/12/2neihcmydm0tpxux/'
        );
        $this->currentScope = $this->B24?->core?->call('scope')->getResponseData()->getResult();
    }

    private static function getContactId() {
        if (!isset($_REQUEST["PLACEMENT_OPTIONS"]) || empty($_REQUEST["PLACEMENT_OPTIONS"])) {
            return 0;
        }
        return (int)htmlentities(json_decode($_REQUEST["PLACEMENT_OPTIONS"])->ID);
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

    public function setItemsPerPage($value) {
        $this->itemsPerPage = max(1, intval($value));
        return $this;
    }

    public function setCurrentPage($value) {
        $this->currentPage = max(1, intval($value));
        return $this;
    }

    protected function hasScope($scope) {
        return $this->B24 && in_array($scope, $this->currentScope);
    }

    protected function log($e, $message = 'Erreur lors de la récupération') {
        echo'<pre>';
        var_dump($e);
        echo'</pre>';
        error_log($message . ': ' . $e->getMessage(), 1, __DIR__ . '/error.log');
    }

    public function getCollection() {
        return $this->activityCollection;
    }

    private function notify($message){
        return $this->B24 && in_array($scope, $this->currentScope);
    }

    public function getResponsible($userId){
        try{
            if (!is_numeric($userId) || !$this->hasScope('user') ||!$this->activityCollection->responsible = $this->B24?->core?->call('user.get', [
                'select' => ['ID', 'NAME', 'LAST_NAME'],
                'ID' => $userId
            ])->getResponseData()->getResult()) {
                throw new Exception('Le module ou scope USER n\'est pas activé');
            }
        }catch(Exception $e){
            $this->log($e,'Erreur lors de la récupération du responsable');
        }
        return $this;
    }

    abstract public function getActivities();
    abstract public function renderActivitiesList();
}
