<?php
namespace NS2B;

require_once __DIR__ . '/base.php';
abstract class CrmCompany extends NsBase{
    protected $B24 = null;
    protected $entityTypeId = '4';
    protected $companyCollection = null;
    protected $itemsPerPage = 10;
    protected $currentPage = 1;
    protected $currentScope = [];
    protected $errorMessages = [];
    protected $requiredScopes = [
        'crm',
        'user'
    ];

    private static function getCompanyId() {
        return self::getContextId();
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

    protected function getCollection() {
        return $this->companyCollection;
    }

    protected function setCollection($value) {
        $this->companyCollection = $value;
    }

    abstract public function getCurrentCompany();
    abstract public function renderCurrentCompany();
}
