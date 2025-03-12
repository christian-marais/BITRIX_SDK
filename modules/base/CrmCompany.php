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
    protected $fields=[
        'siret'=>'UF_CRM_1713268514492',
        'codePostale'=>'',
        'commune'=>'',
        'rue'=>'',
        'ville'=>'',
        'siren'=>'UF_CRM_1713268514493',
        'nom'=>'',
        'acitivite'=>'',
        'naf'=>'',
        'ca'=>'',
        'zoneNS2B_enum'=>'',
        'statut_enum'=>'',
        'activite_enum'=>'',
        'email'=>'',
        'tel'=>'',
        'famille_enum'=>'',
        'nom_mention'=>'',
        'modele_mention'=>'4',//societe,
        'forme_juridique_mention'=>'',
        'ca_mention'=>'',
        'siret_mention'=>'',
        'naf_mention'=>'',
        'rcs_mention'=>'',
        'tva_intracommunautaire_mention'=>'',
        'date_clôture_mention'=>'',
        'identifiant_association_mention'=>'',
        'pappersUrl_mention'=>'UF_CRM_1740987515'
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
