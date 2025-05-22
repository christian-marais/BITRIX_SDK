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
        'bitrix'=>[
            'NextcloudAccount'=>'UF_CRM_1747287863046',
            'NextcloudPassword'=>'UF_CRM_1747287883343',
            'siret'=>'UF_CRM_1713268514492',
            'codePostale_mention'=>'POSTAL_CODE',
            'commune_mention'=>'CITY',
            'rue_mention'=>'ADDRESS_1',
            'ville_mention'=>'CITY',
            'siren'=>'UF_CRM_1713268514493',
            'nom'=>'TITLE',
            'activite'=>'UF_CRM_1714110310933',
            'naf'=>'UF_CRM_1714110310933',
            'ca'=>'',
            'zoneNS2B_enum'=>'',
            'statut_enum'=>'',
            'activite_enum'=>'',
            'email'=>'',
            'tel'=>'',
            'famille_enum'=>'',
            'nom_mention'=>'RQ_COMPANY_NAME',
            'modele_mention'=>'4',//societe,
            'forme_juridique_mention'=>'RQ_LEGAL_FORM',
            'ca_mention'=>'RQ_CAPITAL',
            'siret_mention'=>'RQ_SIRET',
            'siren_mention'=>'RQ_SIREN',
            'naf_mention'=>'RQ_OKVED',
            'rcs_mention'=>'RQ_RCS',
            'tva_intracommunautaire_mention'=>'RQ_VAT_ID',
            'date_cloture_mention'=>'',
            'identifiant_association_mention'=>'',
            'pappersUrl_mention'=>'UF_CRM_1740987515',
            "code"=>"UF_CRM_1746019469733"
        ],
        'annuaire'=>[
            'siret'=>'siret',
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
        ]
    ];
    public function __construct(){
        parent::__construct();
        foreach($this->fields["bitrix"] as $key => $value){
            if(!empty($value = $_ENV[$key]??[])){
                $this->fields["bitrix"][$key] = $value;
            }
        }
    }
    protected $company = [];

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

        if (!$database->entityExists('company')) {
            $companyDbFields = $database->listFields('company');
            $companyFields = $this->fields['bitrix'];
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

    public function getCollection() : ?\stdClass
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
