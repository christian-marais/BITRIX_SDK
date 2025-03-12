<?php
namespace NS2B\SDK\MODULES\BASE;

require_once __DIR__ . '/base.php';
abstract class CrmActivity extends Base{
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

    

    private static function getContactId() {
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
        return $this->activityCollection;
    }

    abstract public function getActivities();
    abstract public function renderActivitiesList();
}
