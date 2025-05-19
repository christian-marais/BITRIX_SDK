<?php
declare(strict_types=1);
namespace NS2B\SDK\MODULES\CRM\COMPANY\INSEE;
use \Exception;
use \DateTime;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use NS2B\SDK\MODULES\BASE\CrmCompany;
use stdClass;

/**
 * Class CompanyComponent
 *
 * Cette classe permet de gestion des entreprises
 */
class CompanyComponent extends CrmCompany{
    /**
     * Collection des entreprises
     * @var \ArrayObject
     */
    protected $companyCollection;
    /**
     * Clef d'identification de l'API Insee
     * @var string
     */
    private $inseeKey="8e5b6d15-6a7f-4dd0-9b6d-156a7f4dd0db";
    /**
     * Options de verification des certificats SSL
     * @var array
     */
    private $HttpOption = [
        'verify_peer' => false,
        'verify_host' => false
    ];

    /**
     * Constructeur
     */
    public function __construct() {
        parent::__construct();
        $this->companyCollection = new \stdClass();
        $this->companyCollection->currentCompany=[
            'SIRET'=>null,
            'SIREN'=>null,
            'legalName'=>null,
            'requisite'=>null,
            'contacts'=>null,
            'annuaire'=>null,
            'bodacc'=>null,
            'boddacAlerts'=>null,
            'insee'=>null,
            'pappers'=>null,
            'societe.comUrl'=>null,
            'pappersUrl'=>null,
            'fields'=>$this->fields
        ];
        $this
            ->getCurrentCompany()
            ->getCompanyRequisite() 
            ->setCompanySourcesUrl();
    }

    /**
     * Recherche l'entreprise actuelle
     *
     * @return $this
     */
    public function getCurrentCompany() {
        try {
            if(!$this->hasScope('crm')){
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            }
            $company = !empty($id=$this->getContextId())?
            $this->B24
                ->core
                ->call('crm.company.get', [
                    'ID' => $id,
                    'SELECT' => [
                        '*',
                        ...$this->fields['bitrix']
                    ]
                ])
                ->getResponseData()
                ->getResult():
            null;
            $this->companyCollection->currentCompany = array_merge($this->companyCollection->currentCompany, $company??[],['fields'=>$this->fields]);
            
            $this->getCompanyContacts();
            $this->setCustomSiret($this->companyCollection->currentCompany[$this->fields['bitrix']["siret"]]);
       } catch (Exception $e) {
            $this->log($e, 'Erreur lors de la récupération de l\'entreprise');
        }finally{
            return $this;
        }
    }

    /**
     * Recherche l'entreprise avec le numéro SIRET
     *
     * @param string $siret
     * @return $this
     */
    public function getCompanyWithSiretFromBitrix(string $siret=null){
        try {
            $request= Request::createFromGlobals();
            $siret=$siret??explode('/',$request->getPathInfo())[2]??$request->query->get('siret');
            if(!is_numeric($siret))
                throw new Exception('Le numéro SIRET est invalide');
            if(($len=strlen($siret))!=14)
                throw new Exception('Le numéro SIRET est trop court ou trop long');
            if(!$this->hasScope('crm'))
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            
            $company = $this->B24
                ->core
                ->call('crm.company.list', [
                    'SELECT' => [
                        '*',...$this->fields['bitrix']
                    ],
                    'FILTER' => [
                        $this->fields['bitrix']["siret"]=> $siret
                    ]
                ])
                ->getResponseData()
                ->getResult()[0]??null;
            $this->companyCollection->currentCompany = $company;
            $this->companyCollection->currentCompany['fields']=$this->fields;
            $this->setCustomSiret($siret);
           
        }catch (Exception $e) {
            $this->log($e, 'Erreur lors de la récupération de l\'entreprise de bitrix  à partir du siret');
        }finally{
            $this->getCompanyContacts();
            return $this;
        }
    }

    /**
     * Met a jour l'entreprise sur bitrix
     *
     * @return $this
     */
    public function updateCompanyToBitrix(array $company=[]){

        try {
            if(!$this->hasScope('crm'))
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            if(empty($company)&&!is_array($company))
                throw new Exception('Les données de l\'entreprise sont invalides');
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
        }finally{
            return $this;
        }
    }

    private function addRequisite($company){
        $requisite=[
            "NAME"=>$company[$this->fields["bitrix"]['nom']],
            "ENTITY_ID"=>$company['ID'],
            "ENTITY_TYPE_ID"=>4,
            'PRESET_ID'=>3,
            $this->fields["bitrix"]['tva_intracommunautaire_mention']=>$company[$this->fields["bitrix"]['tva_intracommunautaire_mention']],
            $this->fields["bitrix"]['naf_mention']=>$company[$this->fields["bitrix"]['naf']],
            $this->fields["bitrix"]['nom_mention']=>$company[$this->fields["bitrix"]['nom']],
            $this->fields["bitrix"]['rcs_mention']=>$company[$this->fields["bitrix"]['rcs_mention']],
            $this->fields["bitrix"]['ca_mention']=>$company[$this->fields["bitrix"]['ca']],
            $this->fields["bitrix"]['forme_juridique_mention']=>$company[$this->fields["bitrix"]['forme_juridique_mention']],
            $this->fields["bitrix"]['siren_mention']=>$company[$this->fields["bitrix"]['siren']],
            $this->fields["bitrix"]['siret_mention']=>$company[$this->fields["bitrix"]['siret']],
            $this->fields["bitrix"]['pappersUrlMention']=>$company[$this->fields["bitrix"]['pappersUrlMention']],
            $this->fields["bitrix"]['date_cloture_mention']=>$company[$this->fields["bitrix"]['date_cloture']],
        ];
        unset($requisite[""]);
        return $requisite= $this->B24
            ->core
            ->call('crm.requisite.add', [
                'fields' =>$requisite
            ])
            ->getResponseData()
            ->getResult()[0];
    }

    private function addAddress($company){
        $address=[
            "ENTITY_ID"=>$company["ID"],
            "ENTITY_TYPE_ID"=>4,
            "TYPE_ID"=>1,//crm.enum.adressType
            "COUNTRY_ID"=>"FR",
            "REGION"=>"",
            $this->fields["bitrix"]['commune_mention']=>$company[$this->fields["bitrix"]['commune_mention']],
            $this->fields["bitrix"]['codePostale_mention']=>$company[$this->fields["bitrix"]['codePostale_mention']],
            $this->fields["bitrix"]['rue_mention']=>$company[$this->fields["bitrix"]['rue_mention']]
        ];
        unset($address[""]);
        return $address= $this->B24
            ->core
            ->call('crm.address.add', [
                'fields' =>$address
            ])
            ->getResponseData()
            ->getResult()[0];
    }

    /**
     * Ajoute l'entreprise à bitrix
     *
     * @return $this
     */
    public function addCompanyToBitrix(array $company=[]){

        try {
            if(!$this->hasScope('crm'))
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            if(empty($company)&&!is_array($company))
                throw new Exception('Les données de l\'entreprise sont invalides');
           
                $company["ID"]= $this->B24
                ->core
                ->call('crm.company.add', [
                    // 'entityTypeId' => 4,
                    'fields' =>$company
                ])
                ->getResponseData()
                ->getResult()[0];
            $requisite=$this->addRequisite($company);
            $address=$this->addAddress($company);
         
            return [
                'status' => 'success',
                'message' => 'Entreprise ajoutée avec succès:'.$company["ID"],
                'result' => $company["ID"]
            ];
            
        }catch (Exception $e) {
            $this->log($e, 'Erreur lors de l\'ajout de l\'entreprise');
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }


    /**
     * Recherche les détails de l'entreprise dans les mentions
     *
     * @return $this
     */
    public function getCompanyRequisite(){
        try {
           
            if(!$this->hasScope('crm')){
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            }

            $requisite = !empty($id=$this->getContextId()??$this->companyCollection->currentCompany['ID'])?
            $this->B24
                ->core
                ->call('crm.requisite.list', [
                'filter' => [
                    'ENTITY_ID' => $id,
                    'ENTITY_TYPE_ID' => $this->entityTypeId
                ],
                'select' => [$this->fields['bitrix']["pappersUrl_mention"],'*']
                ])
                ->getResponseData()
                ->getResult()[0]??null
                :null;
                $this->companyCollection->currentCompany['requisite'] = !empty($requisite)?$requisite:null;

        } catch (Exception $e) {
            $this->log($e, 'Erreur lors de la récupération des détails de l\'entreprise');
        }finally{
            return $this;
        }
    }


    


    /**
     * Set the company sources url
     *
     * @return $this
     */
    public function setCompanySourcesUrl(){
        try{
            if(!isset($this->companyCollection->currentCompany)){
                $this->companyCollection->currentCompany=[];
                throw new Exception('Les données de l\'entreprise sont invalides, l\'entreprise n\'a pas été trouvéepas été récupérée depsui bitrix');
           
            }
            if(empty($siret=$this->companyCollection->currentCompany['SIRET']))
                throw new Exception('Le SIRET de l\'entreprise est invalide :'.$siret);
            $siren=$this->companyCollection->currentCompany['SIREN']??=substr($siret, 0, 9);

            $bodacc="https://bodacc-datadila.opendatasoft.com/api/records/1.0/search/?dataset=annonces-commerciales&sort=dateparution&refine.familleavis_lib=Procédures+collectives&q=";
            $pappers="https://www.pappers.fr/recherche?q=";
            $societeCom="https://www.societe.com/societe/";
            $pagesJaunes="https://www.pagesjaunes.fr/siret/";
            
            $requisite=$this->companyCollection->currentCompany['requisite']??[];
            $this->companyCollection->currentCompany['pappersUrl']=!empty($requisite[$this->fields['bitrix']["pappersUrl_mention"]??''])?$requisite[$this->fields['bitrix']["pappersUrl_mention"]]:$pappers.$siret;
            $this->companyCollection->currentCompany['annuaireUrl'] ='https://annuaire-entreprises.data.gouv.fr/etablissement/'.$siret;
            $this->companyCollection->currentCompany['pagesJaunesUrl']=$pagesJaunes.$siret;
            $this->companyCollection->currentCompany['societe.comUrl']=$societeCom.strtolower(str_replace([' ', '&', '_' , '\'', '-'], '-', $this->companyCollection->currentCompany['legalName']??'').'-'.$siren.'.html');
        
        }catch(Exception $e){
            $this->log($e, 'Erreur lors de la mise à jour des sources de l\'entreprise');
        }
        return $this;
    }

    /**
     * Recherche l'entreprise sur l'annuaire entreprise
     *
     * @return $this
     */
    public function getCompanyFromBodacc(){
        try {
            if(empty($this->companyCollection->currentCompany["SIREN"])||!is_numeric($this->companyCollection->currentCompany["SIREN"]))
                throw new Exception('Siren invalide');
            $client = HttpClient::create($this->HttpOption);
            $response = $client->request('GET', 'https://bodacc-datadila.opendatasoft.com/api/records/1.0/search/?dataset=annonces-commerciales&sort=dateparution&refine.familleavis_lib=Procédures+collectives&q='.$this->companyCollection->currentCompany["SIREN"]);
            
            if ($response->getStatusCode() != 200)
                throw new Exception('Erreur lors de la récupération du bodacc');
            $this->companyCollection->currentCompany['bodacc']=json_decode($response->getContent()??'{}')?->records;
            $this->setBodaccCustomRecord();

        } catch (Exception $e) {
            $this->log($e, $e->getMessage());
        }finally{
            return $this;
        }
    }

    /**
     * Recherche l'entreprise sur l'annuaire entreprise
     *
     * @return $this
     */
    public function setBodaccCustomRecord(){
        try{
            if($records=$this->companyCollection->currentCompany['bodacc']){
                foreach ($records as $record) {
                    $jugement=json_decode($record->fields->jugement??'{}');
                    $this->companyCollection->currentCompany['bodaccRecords'][]=[
                        'dateparution'=>$record->fields->dateparution,
                        'siren'=>json_decode($record->fields->listepersonnes??'{}')?->numeroIdentifiant??'',
                        'datejugement'=>$jugement?->date??'',
                        'numeroAnnonce'=>$record->fields->numeroAnnonce??'',
                        'registre'=>substr($record->fields->registre??'',0,strpos($record->fields->registre??'',',')??0),
                        'jugement'=>$jugement?->nature,
                        'commercant'=>$record->fields->commercant??'',
                        'tribunal'=>$record->fields->tribunal??'',
                        'url_complete'=>$record->fields->url_complete??'',
                        'familleavis_lib'=>$record->fields->familleavis_lib??'',
                        'description'=>$jugement?->complementJugement??'',
                        'type'=>$record->fields->typeavis_lib??'',
                        'ville'=>$record->fields->ville??'',
                    ];
                }
            }   
        }catch(Exception $e){
            $this->log($e, $e->getMessage());
        }    
        return $this;
    }


    /**
     * Recherche l'entreprise sur l'annuaire entreprise
     *
     * @return $this
     */
    public function getCompanyFromInsee(){
        
        try {
            if(empty($this->companyCollection->currentCompany["SIRET"])||!is_numeric($this->companyCollection->currentCompany["SIRET"]))
            throw new Exception('Siret invalide');
            $this->HttpOption["headers"] = [
                'X-INSEE-Api-Key-Integration' => $this->inseeKey,
            ];

            $client = HttpClient::create($this->HttpOption);
            
            $response = $client->request('GET', 'https://api.insee.fr/api-sirene/3.11/siret/'.$this->companyCollection->currentCompany["SIRET"]);
          
            if ($response->getStatusCode() != 200) {
                throw new Exception('Erreur lors de la récupération INSEE de l\'entreprise');
            }

            $this->companyCollection->currentCompany['insee']=$company=json_decode($response->getContent()??'{}');
            $this->companyCollection->currentCompany["legalForm"]=!empty($company)?$this->companyCollection->currentCompany['insee']->legalForm=$this->getCategoryLabel($company?->etablissement->uniteLegale->categorieJuridiqueUniteLegale??''):null;
            $this->companyCollection->currentCompany["legalName"]=$company->etablissement->uniteLegale->denominationUniteLegale??($company->etablissement->uniteLegale->nomUniteLegale.' '.$company->etablissement->uniteLegale->prenom1UniteLegale);

        } catch (Exception $e) {
            $this->log($e, $e->getMessage());
        }finally{
            return $this;
        }
    }

    private function getCategoryLabel(string $code,$categorie='categorie_juridique'): string {
        $label = $code;
        $file=__DIR__ . '/const/'.$categorie.'.php';
        if(file_exists($file)){
            $categories = require $file;
            if (!empty($code) && is_array($categories)) {
                $label = $categories[$code] ?? $code;
            }
        }
        return $label;
    }

   public function getCategoryLabelFromText($code, $categorie = 'naf') {
       $file = __DIR__ . '/const/' . $categorie . '.txt';
       $newArray = []; // Initialize the array to avoid undefined variable notice
   
       if (file_exists($file)) {
           $data = file_get_contents($file);
           $lines = explode(PHP_EOL, $data); // Split the data into lines
   
           foreach ($lines as $line) {
               if (preg_match('/^(\S+)\s+(.*)$/', $line, $matches)) {
                   $newArray[$matches[1]] = $matches[2]; // Create associative array
               }
           }
       }
       return $newArray[$code] ?? $code; // Return the label or the code if not found
   }
    /**
     * Recherche l'entreprise sur l'annuaire entreprise
     *
     * @return $this
     */
    public function getCompanyFromAnnuaire(){
        error_log('Starting getCompanyFromAnnuaire');
        try {
            
            error_log('Processing data...');
            if(empty($this->companyCollection->currentCompany)||empty($siret=$this->companyCollection->currentCompany["SIRET"])||!is_numeric($siret))
                throw new Exception('Siret invalide');
            $siren=$this->companyCollection->currentCompany["SIREN"]??substr($siret, 0, 9);
            $client = HttpClient::create($this->HttpOption);
            $response = $client->request('GET', 'https://recherche-entreprises.api.gouv.fr/search?q='.$siret.'&page=1&per_page=20');
          
            if ($response->getStatusCode() != 200) 
                throw new Exception('Erreur lors de la récupération de l\'annuaire entreprise');
            
            $annuaire=json_decode($response->getContent())->results[0];
            
            $this->companyCollection->currentCompany['legalName']=$annuaire?->nom_complet;
            $this->companyCollection->currentCompany["libelle_activite"]=!empty($annuaire)?$annuaire->libelle_activite_principale=$this->getCategoryLabelFromText($annuaire?->siege->activite_principale??'','naf'):null;
            if($dirigeant=$annuaire?->dirigeants[0]??null)
                $annuaire->dirigeant=$dirigeant->nom.' '.$dirigeant->prenoms;
            $annuaire->matching_etablissements[]=$annuaire->siege;
            $sirets=[];
            foreach($annuaire->matching_etablissements as $key =>$etablissement){
                $annuaire->matching_etablissements[$key]->forme_juridique??=$this->getCategoryLabel($annuaire->nature_juridique??'');
                $annuaire->matching_etablissements[$key]->nom_complet??=$annuaire->nom_complet;
                $annuaire->matching_etablissements[$key]->libelle_activite_principale=$this->getCategoryLabelFromText($etablissement?->activite_principale??'','naf');
                if(!empty($etablissement->siret) && in_array($etablissement->siret,$sirets)){
                    unset($annuaire->matching_etablissements[$key]);
                }
                $sirets[]=$etablissement->siret;
            }
            
            error_log('First request ending...');
            if($annuaire->nombre_etablissements>1){
                error_log('Requesting more result starting...');
                $newResponse = json_decode($client->request('GET', $url='https://recherche-entreprises.api.gouv.fr/search?q='.($annuaire->nom_complet??$annuaire->dirigeant).'&page=1&per_page=20')->getContent());
               
               foreach($newResponse->results as $society){
                    
                    if($society->siren==$siren){ 
                        $sirets=array_map(function($etablissement){
                            return $etablissement->siret;
                        },$annuaire->matching_etablissements);
                        foreach($society->matching_etablissements as $key => $etablissement){
                            if(!empty($etablissement->siret) && !in_array($etablissement->siret,$sirets)){
                                $etablissement->forme_juridique=$this->getCategoryLabel($society?->nature_juridique??'');
                                $etablissement->libelle_activite_principale=$this->getCategoryLabelFromText($etablissement?->activite_principale??'','naf');
                                $etablissement->nom_complet=$society->nom_complet;
                                $annuaire->matching_etablissements[$key]=$etablissement;
                                
                            }
                        }
                      
                    }
                }
                error_log('Requesting more result ending...');
                if($newResponse->total_pages>1){
                    error_log('Requesting more result from more page starting...');
                    do{
                        $goNext=true;
                        $url='https://recherche-entreprises.api.gouv.fr/search?q='.($annuaire->nom_complet??$annuaire->dirigeant).'&page='.(++$newResponse->page).'&per_page=20';
                     
                        $newResponse = json_decode($client->request('GET', $url)->getContent());
                       
                        foreach($newResponse->results as $society){
                            if($society->siren==$siren){ 
                                $sirets=array_map(function($etablissement){
                                    return $etablissement->siret;
                                },$annuaire->matching_etablissements);
                                foreach($society->matching_etablissements as $etablissement){
                                    if($etablissement->siret!==$siret && !in_array($etablissement->siret,$sirets)){
                                        $etablissement->libelle_activite_principale=$this->getCategoryLabelFromText($etablissement?->activite_principale??'','naf');
                                        $etablissement->forme_juridique=$this->getCategoryLabel($society?->nature_juridique??'');
                                        $etablissement->nom_complet=$society->nom_complet;
                                        $annuaire->matching_etablissements[$etablissement->siret]=$etablissement;

                                    }
                                }
                               $goNext=false;
                            }
                       }
                    }while($newResponse->page<$newResponse->total_pages && $goNext && $newResponse->page<10 && count($annuaire->matching_etablissements)<=$annuaire->nombre_etablissements);
                }
                error_log('Requesting more result from more page ending...');
            }
            error_log('Requesting more result ending...');
        } catch (Exception $e) {
            $this->log($e, $e->getMessage());
        }finally{
            error_log('Completed getCompanyFromAnnuaire');
            $this->companyCollection->currentCompany['annuaire']=$annuaire??[];
            return $this;
        }
    }
    
    public function getBodaccAlerts(array $sirens=[],$request = null):self{
        try {
            _error_log('Processing getBodaccAlerts');
            if(empty($sirens)||!is_array($sirens))
                throw new Exception('Il n\'est pas donné un tableau de sirets valide');
            $whereDate=$request->query->get('wheredate')??'>=';
            $client = HttpClient::create($this->HttpOption);
            $date= "and dateparution $whereDate date'".$request->query->get('date')??(new DateTime('today'))->format('Ymd')."'";
            $sirens=implode(',',array_map(function ($siren){
                return "'$siren'";
            }, $sirens));

            $where=str_replace(" ","%20", "registre in ($sirens)");
            $url='https://bodacc-datadila.opendatasoft.com/api/explore/v2.1/catalog/datasets/annonces-commerciales/records?where='.$where.$date.'&limit=100&refine=familleavis_lib%3AProc%C3%A9dures%20collectives';
            $response = $client->request('GET', $url);
            
            if ($response->getStatusCode() != 200)
                throw new Exception('Erreur lors de la récupération des annonces bodacc entreprise');
            
            $results=json_decode($response->getContent())->results;
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
                    'url'=>$alerte->url_complete??'',
                    'siren'=>$alerte->registre[0]??''
                ];
            }
            _error_log('Ending getBodaccAlerts');
            $this->companyCollection->currentCompany['bodaccAlerts']=$alertes;
        }catch (Exception $e) {
            _error_log($e->getMessage());
            $this->log($e, $e->getMessage());
        }finally{
            return $this;
        }
    }


    public function setInseeKey($key){
        $this->inseeKey = $key;
        return $this;
    }
    public function setCustomSiret($siret=''){
        $this->companyCollection->currentCompany["SIRET"] = ($siret=str_replace(' ','',$siret));
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
    public function getCompany(){
        return $this->companyCollection->currentCompany;
    }

    public function getCompanyById($company):array{
        
        $company=$this->B24
        ->core
        ->call('crm.company.get', 
        ['ID'=>$company["id"],
        'select'=>['*']
        ])
        ->getResponseData()
        ->getResult();
        return $this->companyCollection->currentCompany=$company;
        
    }

    public function getCompanyContacts(){
        _error_log('Processing getCompanyContacts');
        if( !array_key_exists('ID',$this->companyCollection->currentCompany) ||
            empty($id=$this->companyCollection->currentCompany['ID'])){
            return $this;
        }
        try{
            $contacts=$this->B24
            ->core
            ->call(
                'crm.company.contact.items.get',
                ['ID'=>$id]
            )
            ->getResponseData()
            ->getResult();
            foreach($contacts as $contact){
                $result[]=$this->B24
                ->core
                ->call('crm.contact.get', 
                ['ID'=>$contact["CONTACT_ID"],
                'select'=>['*']
                ])
                ->getResponseData()
                ->getResult();
            }
            $this->companyCollection->currentCompany['contacts']=$result;
        
        }catch(Exception $e){
            _error_log('Error getCompanyContacts: '.$e->getMessage());
            $this->log($e, $e->getMessage());
        }finally{
            _error_log('Ending getCompanyContacts');
            return $this;
        }
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
            case true||empty($company['SIREN']):
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