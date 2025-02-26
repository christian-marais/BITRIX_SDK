<?php
declare(strict_types=1);
namespace NS2B;
use \Exception;
require_once dirname(__DIR__, 2) . '/modules/base/CrmCompany.php';
// require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");


class NSCompanyPappers extends CrmCompany{
    protected $companyCollection;
    private $companyPappersUrlField = 'UF_CRM_1740987515';
    private$companySiretField = 'UF_CRM_1713268514492';

    public function __construct() {
        parent::__construct();
        $this->companyCollection = new \stdClass();
    }

    public function getCurrentCompany() {
        
        try {
            if(!$this->hasScope('crm')){
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            }

            $company = $this->B24
                ->core
                ->call('crm.company.get', ['ID' => 3008])
                ->getResponseData()
                ->getResult();
            $company['COMPANY_SIREN'] = $company[$this->companySiretField];
            $this->companyCollection->currentCompany = $company;

            
        } catch (Exception $e) {
            $this->log($e, 'Erreur lors de la récupération de l\'entreprise');
        }
       
 
        return $this;
    }

    public function getCompanyRequisite(){
        try {
            $bodacc="https://bodacc-datadila.opendatasoft.com/api/records/1.0/search/?dataset=annonces-commerciales&sort=dateparution&refine.familleavis_lib=Procédures+collectives&q=";
            $pappers="https://www.pappers.fr/recherche?q=";
            $societeCom="https://www.societe.com/societe";
            if(!$this->hasScope('crm')){
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            }

            $requisite = $this->B24
                ->core
                ->call('crm.requisite.list', [
                'filter' => [
                    'ENTITY_ID' => 3008,
                    'ENTITY_TYPE_ID' => $this->entityTypeId
                ],
                'select' => [$this->companyPappersUrlField,'*']
                ])
                ->getResponseData()
                ->getResult()[0]??null;
                $this->companyCollection->currentCompany['COMPANY_PAPERS_URL']=!empty($requisite[$this->companyPappersUrlField])?$requisite[$this->companyPappersUrlField]:$pappers.$this->companyCollection->currentCompany[$this->companySiretField];
                $this->companyCollection->currentCompany['requisite'] = !empty($requisite)?$requisite:null;
                
            
        } catch (Exception $e) {
            $this->log($e, 'Erreur lors de la récupération des détails de l\'entreprise');
        }
 
        return $this;
    }

    public function getCompanyFromInsee($siret){

        
    }

    public function redirectToPappers(){
        $company= $this->companyCollection->currentCompany;
        echo '
      
        <!DOCTYPE html>
        <html>
        
        <body>
            <script type="text/javascript">window.open("'.$company['COMPANY_PAPERS_URL'].'","_target");</script>
        </body>
        </html>';
        exit;
    }

    public function renderCurrentCompany(){
        $company= $this->companyCollection->currentCompany;
        include dirname(__FILE__) . '/template.php';
    }
  
}

?>