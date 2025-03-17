<?php
namespace NS2B\SDK\MODULES\BASE;
use NS2B\SDK\Database\DatabaseSQLite;   
use \Exception;
abstract class CrmCompany extends Base{
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
        'activite'=>'',
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

    public function saveCompanyToDb(): void
    {
        $database = new DatabaseSQLite('crmcompanyinsee.db');
        if (!$database->dbExists()) {
            $database->createDatabase('crmcompanyinsee.db');
        }

        if ($database->entityExists('company') || $database->createEntity('company')) {
            $companyDbFields = $database->listFields('company');
            $companyFields = $this->fields;
            $fields = [];
            
            foreach ($companyFields as $field => $value) {
                if (!in_array($value, $companyDbFields)) {
                    $fields[] = $value;
                }
            }
            
            if (!empty($fields)) {
                $database->createEntity('company', $fields);
            }
        }

        if ($this->companyCollection && isset($this->companyCollection->currentCompany)) {
            $database->insert('company', $this->companyCollection->currentCompany);
        }
    }

    public function getCollection(): ?array 
    {
        return $this->companyCollection;
    }

    protected function setCollection(?array $value): void 
    {
        $this->companyCollection = $value;
    }
    
    abstract public function getCurrentCompany();
    abstract public function renderCurrentCompany();
}
