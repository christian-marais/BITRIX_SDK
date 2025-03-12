<?php
declare(strict_types=1);
namespace NS2B;
use \Exception;
use \DateTime;
use Symfony\Component\HttpClient\HttpClient;
require_once dirname(__DIR__, 2) . '/modules/base/CrmCompany.php';
// require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");


class NSCompanyPappers extends CrmCompany{
    protected $companyCollection;
    private $inseeKey="8e5b6d15-6a7f-4dd0-9b6d-156a7f4dd0db";
    private $HttpOption = [
        'verify_peer' => false,
        'verify_host' => false
    ];

    public function __construct() {
        parent::__construct();
        $this->companyCollection = new \stdClass();
        $this
            ->setAction()
            ->checkRequiredScopes()
            ->setItemsPerPage($itemsPerPage)
            ->setCurrentPage($currentPage)
            ->getCurrentCompany()
            ->setCompanySourcesUrl();
    }

    public function setAction(){
        $this->action[]=htmlspecialchars(strip_tags($_GET['action']??''));
        return $this;
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
            $this->companyCollection->currentCompany = $company;
            $this->setCustomSiret($this->companyCollection->currentCompany[$this->fields["siret"]]);
        } catch (Exception $e) {
            $this->log($e, 'Erreur lors de la récupération de l\'entreprise');
        }
       
        return $this;
    }

    public function getCompanyWithSiretFromBitrix(string $siret=null){

        try {
            $siret=isset($_GET["siret"])?$siret??htmlspecialchars($_GET["siret"]):null;
            if(!$this->hasScope('crm'))
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            if(!is_numeric($siret))
                throw new Exception('Le numéro SIRET est invalide');
            

            $company = $this->B24
                ->core
                ->call('crm.company.list', [
                    'SELECT' => [
                        '*'
                    ],
                    'FILTER' => [
                        $this->companySiretField => $siret
                    ]
                ])
                ->getResponseData()
                ->getResult()[0];
            $this->companyCollection->currentCompany = $company;
            $this->setCustomSiret($siret);
            
        }catch (Exception $e) {
            $this->log($e, 'Erreur lors de la récupération de l\'entreprise de bitrix  à partir du siret');
        }
        return $this;
    }

    public function updateCompanyToBitrix(array $company=[]){

        try {
            if(!$this->hasScope('crm'))
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            $company = $this->B24
                ->core
                ->call('crm.item.update', [
                    'entityTypeId' => 4,
                    'fields' =>$company
                ])
                ->getResponseData()
                ->getResult();
            
        }catch (Exception $e) {
            $this->log($e, 'Erreur lors de la mise à jour de l\'entreprise');
        }
        return $this;
    }

    public function addCompanyToBitrix(array $company=[]){

        try {
            if(!$this->hasScope('crm'))
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            

            $company = $this->B24
                ->core
                ->call('crm.item.add', [
                    'entityTypeId' => 4,
                    'fields' =>$company
                ])
                ->getResponseData()
                ->getResult();
            $company=array_merge($company,["ENTITY_TYPE_ID"=>4]);
            $requisite= $this->B24
                ->core
                ->call('crm.requisite.add', [
                    'fields' =>$company
                ])
                ->getResponseData()
                ->getResult();
           
            
            
        }catch (Exception $e) {
            $this->log($e, 'Erreur lors de la mise à jour de l\'entreprise');
        }
        return $this;
    }


    public function getCompanyRequisite(){
        try {
           
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
                'select' => [$this->fields["pappersUrl_mention"],'*']
                ])
                ->getResponseData()
                ->getResult()[0]??null;
                $this->companyCollection->currentCompany['requisite'] = !empty($requisite)?$requisite:null;

        } catch (Exception $e) {
            $this->log($e, 'Erreur lors de la récupération des détails de l\'entreprise');
        }
 
        return $this;
    }

    public function setCompanySourcesUrl(){
        $bodacc="https://bodacc-datadila.opendatasoft.com/api/records/1.0/search/?dataset=annonces-commerciales&sort=dateparution&refine.familleavis_lib=Procédures+collectives&q=";
        $pappers="https://www.pappers.fr/recherche?q=";
        $societeCom="https://www.societe.com/societe/";
        $pagesJaunes="https://www.pagesjaunes.fr/siret/";

        $requisite=$this->companyCollection->currentCompany['requisite'];
        $this->companyCollection->currentCompany['pappersUrl']=!empty($requisite[$this->fields["pappersUrl_mention"]])?$requisite[$this->fields["pappersUrl_mention"]]:$pappers.$this->companyCollection->currentCompany[$this->fields["siret"]];
        $this->companyCollection->currentCompany['annuaireUrl'] ='https://annuaire-entreprises.data.gouv.fr/etablissement/'.$this->companyCollection?->currentCompany["SIRET"];
        $this->companyCollection->currentCompany['pagesJaunesUrl']=$pagesJaunes.$this->companyCollection?->currentCompany["SIRET"];
        $this->companyCollection->currentCompany['societe.comUrl']=$societeCom.strtolower(str_replace([' ', '&', '_' , '\'', '-'], '-', $this->companyCollection->currentCompany['legalName']??'').'-'.$this->companyCollection->currentCompany['SIREN'].'.html');
    }

    public function getCompanyFromBodacc(){
        try {
            $client = HttpClient::create($this->HttpOption);
            $response = $client->request('GET', 'https://bodacc-datadila.opendatasoft.com/api/records/1.0/search/?dataset=annonces-commerciales&sort=dateparution&refine.familleavis_lib=Procédures+collectives&q='.$this->companyCollection->currentCompany["SIREN"]);
            
            if ($response->getStatusCode() != 200) {
                throw new Exception('Erreur lors de la récupération du bodacc');
            }
            $this->companyCollection->currentCompany['bodacc']=json_decode($response->getContent())->records;
            $this->setBodaccCustomRecord();
        } catch (Exception $e) {
            $this->log($e, $e->getMessage());
        }
        return $this;
    }
    public function setBodaccCustomRecord(){
        try{
            if($records=$this->companyCollection->currentCompany['bodacc']){
                foreach ($records as $record) {
                    $this->companyCollection->currentCompany['bodaccRecords'][]=[
                        'dateparution'=>$record->fields->dateparution,
                        'siren'=>json_decode($record->fields->listepersonnes??'{}')?->numeroIdentifiant??'',
                        'datejugement'=>$record->fields->date??'',
                        'numeroAnnonce'=>$record->fields->numeroAnnonce??'',
                        'registre'=>substr($record->fields->registre??'',0,strpos($record->fields->registre??'',',')??0),
                        'jugement'=>json_decode($record->fields->jugement??'{}')?->nature,
                        'commercant'=>$record->fields->commercant??'',
                        'tribunal'=>$record->fields->tribunal??'',
                        'url_complete'=>$record->fields->url_complete??'',
                        'familleavis_lib'=>$record->fields->familleavis_lib??'',
                        'description'=>json_decode($record->fields->jugement??'{}')?->complementJugement??'',
                        'type'=>$record->fields->typeavis_lib??'',
                        'ville'=>$record->fields->ville??'',
                    ];
                }
            }
            // var_dump($this->companyCollection->currentCompany['bodaccRecords']);die();
        }catch(Exception $e){
            $this->log($e, $e->getMessage());
        }    
        return $this;
    }


    public function getCompanyFromInsee(){
        try {
            $this->HttpOption["headers"] = [
                'X-INSEE-Api-Key-Integration' => $this->inseeKey,
            ];
            $client = HttpClient::create($this->HttpOption);
            $response = $client->request('GET', 'https://api.insee.fr/api-sirene/3.11/siret/'.$this->companyCollection->currentCompany["SIRET"]);
          
            if ($response->getStatusCode() != 200) {
                throw new Exception('Erreur lors de la récupération INSEE de l\'entreprise');
            }
            $this->companyCollection->currentCompany['insee']=json_decode($response->getContent());
            $this->companyCollection->currentCompany['legalName']=$this->companyCollection->currentCompany['insee']->etablissement->uniteLegale->denominationUniteLegale;
            
        } catch (Exception $e) {
            $this->log($e, $e->getMessage());
        }
        return $this;
    }

    public function getCompanyFromAnnuaire(){
        try {
            $client = HttpClient::create($this->HttpOption);
            $response = $client->request('GET', 'https://recherche-entreprises.api.gouv.fr/search?q='.$this->companyCollection->currentCompany["SIRET"].'&page=1&per_page=1');
          
            if ($response->getStatusCode() != 200) {
                throw new Exception('Erreur lors de la récupération de l\'annuaire entreprise');
            }
            $this->companyCollection->currentCompany['annuaire']=json_decode($response->getContent())->results[0];
            $this->companyCollection->currentCompany['legalName']=$this->companyCollection->currentCompany['annuaire']?->nom_complet;
            
        } catch (Exception $e) {
            $this->log($e, $e->getMessage());
        }
        return $this;
    }
    
    public function getBodaccAlerts(array $sirets=null):self{
        if(empty($sirets)) return $this;
        try {
            $client = HttpClient::create($this->HttpOption);
            $date= "and dateparution=date'".(new DateTime('today'))->format('Ymd')."'";
            $date='';
            $sirets=urlencode(implode(',',array_map(function ($siret){
                return "'$siret'";
            }, $sirets)));
            $where=str_replace(" ","%20", "registre in $sirets $date");
            $response = $client->request('GET', 'https://bodacc-datadila.opendatasoft.com/api/explore/v2.1/catalog/datasets/annonces-commerciales/records?where=registre%20in('.$sirets.')&limit=100&refine=familleavis_lib%3AProc%C3%A9dures%20collectives');
            if ($response->getStatusCode() != 200) {
                throw new Exception('Erreur lors de la récupération des annonces bodacc entreprise');
            }
            $results=json_decode($response->getContent())->results;
            $this->dd($results);
            foreach($results as $alerte){
                $personnes=json_decode($alerte->listepersonnes??'{}');
                $jugement=json_decode($alerte->jugement??'{}');
                $alertes[str_replace(" ","", $alerte->registre[0]??'')]=[
                    'jugement'=>$jugement->nature??'',
                    'contenu'=>$jugement->complementJugement??'',
                    'datejugement'=>$jugement->date??'',
                    'id'=>$alerte->id??'',
                    'activite'=>$personnes->activite??'',
                    'forme juridique'=>$personnes->forme_juridique??'',
                    'adresse'=>$personnes->numeroVoie??''.' '.$personnes->typeVoie??''.' '.$personnes->nomVoie??''.' '.$personnes->codePostal??''.' '.$personnes->ville??'',
                    'typePersonne'=>$personnes->typePersonne??'',
                    'nom'=>$personnes->nom??'',
                    'dateparution'=>$alerte->dateparution??'',
                    'url'=>$alerte->url_complete??''
                ];
            }
           
            $this->companyCollection->currentCompany['bodaccAlerts']=$alertes;
        } catch (Exception $e) {
            $this->log($e, $e->getMessage());
        }
        return $this;
    }


    public function setInseeKey($key){
        $this->inseeKey = $key;
        return $this;
    }
    public function setCustomSiret($siret=''){
        $this->companyCollection->currentCompany["SIRET"] = $siret;
        $this->companyCollection->currentCompany["SIREN"] =substr($siret, 0, 9);
        return $this;
    }

    public function redirectToPappers(){
        $this->redirectTo('redirectToPappers', $this->companyCollection->currentCompany['pappersUrl']);
        return $this;
    }

    public function redirectTo(string $_GET_Action, string $url){
        if(!in_array($_GET_Action, $this->action)){
            return $this;
        }
        echo '
        <!DOCTYPE html>
        <html>
        <body>
            <script type="text/javascript">window.open("'.$url.'","_target");</script>
        </body>
        </html>';
        exit;
    }



    public function renderCurrentCompany(){
        header('User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:84.0) Gecko/20100101 Firefox/84.0');
        header('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8');
        header('Accept-Language: fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3');
        header('Accept-Encoding: gzip, deflate, br');
        header('Connection: keep-alive');
        header('Upgrade-Insecure-Requests: 1');
        header('Cache-Control: max-age=0');
        $company= $this->companyCollection->currentCompany;
        switch (true) {
            case empty($company['SIREN']):
                include dirname(__FILE__) . '/templates/templateblank.php';
                break;
        
            default:
                include dirname(__FILE__) . '/templates/companyPresentation.php';
                break;
        }
    }
    public function ajaxBoddacAlerts(){
        return $this;
    }

    public function processCompanyData($data) {
        // TO DO: implement data processing logic here
        return $data;
    }

    public function searchCompany($query) {
        // TO DO: implement search logic here
        // You can use the fetchCompanyData method to retrieve data from the API
        // and then process the data using the processCompanyData method
    }
  
}

?>