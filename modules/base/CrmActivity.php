<?php
namespace NS2B;

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
        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
        $this->B24 = ServiceBuilderFactory::createServiceBuilderFromWebhook(
            'https://bitrix24demoec.ns2b.fr/rest/12/2neihcmydm0tpxux/'
        );
        $this->currentScope = $this->B24?->core?->call('scope')->getResponseData()->getResult();
    }

    public function checkRequiredScopes() {
        $missingScopes = array_diff($this->requiredScopes, $this->currentScope);
        if (!empty($missingScopes)) {
            $this->errorMessages[] = "Scopes manquants : " . implode(', ', $missingScopes);
        }
        return $this;
    }

    public function setItemsPerPage($count) {
        $this->itemsPerPage = max(1, intval($count));
        return $this;
    }

    public function setCurrentPage($page) {
        $this->currentPage = max(1, intval($page));
        return $this;
    }

    protected function hasScope($scope) {
        return $this->B24 && in_array($scope, $this->currentScope);
    }

    protected function log($e, $message = 'Erreur lors de la récupération') {
        error_log($message . ': ' . $e->getMessage(), 1, __DIR__ . '/error.log');
    }

    public function getCollection() {
        return $this->activityCollection;
    }

    abstract public function getActivities();
    abstract public function renderActivitiesList();
}
?>
