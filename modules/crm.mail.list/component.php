<?php
declare(strict_types=1);
namespace NS2B;
use \Exception;
// require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

use Bitrix24\SDK\Services\ServiceBuilderFactory;

class NSContactMailActivity {
    private $B24 = null;
    private $NSContactMailActivityCollection = null;
    private $itemsPerPage = 10; // Valeur par défaut
    private $currentPage = 1;
    private $currentScope = [];
    private $errorMessages=[];
    // Scopes nécessaires pour le fonctionnement de la classe
    private $requiredScopes = [
        'crm',   // Pour les activités CRM
        'user'   // Pour récupérer les informations des utilisateurs
    ];

    public function __construct() {
        $this->NSContactMailActivityCollection = new \stdClass();
        require_once dirname(__DIR__, 2) . '/vendor/autoload.php'; 
        $this->B24 = ServiceBuilderFactory::createServiceBuilderFromWebhook(
            'https://bitrix24demoec.ns2b.fr/rest/12/2neihcmydm0tpxux/'
        );
        $this->currentScope = $this->B24?->core?->call('scope')->getResponseData()->getResult();
    }

    // Vérifier si tous les scopes requis sont présents
    public function checkRequiredScopes() {
        $missingScopes = array_diff($this->requiredScopes, $this->currentScope);
        if (!empty($missingScopes)) {
            $this->errorMessages[] = "Scopes manquants : " . implode(', ', $missingScopes);
        }
        return $this;
    }

    private static function getContactId() {
        if (!isset($_REQUEST["PLACEMENT_OPTIONS"]) || empty($_REQUEST["PLACEMENT_OPTIONS"])) {
            return 0;
        }
        return (int)htmlentities(json_decode($_REQUEST["PLACEMENT_OPTIONS"])->ID);
    }

    // Nouvelle méthode pour définir le nombre d'articles par page
    public function setItemsPerPage($count) {
        $this->itemsPerPage = max(1, intval($count)); // Minimum 1 article par page
        return $this;
    }

    // Nouvelle méthode pour définir la page courante
    public function setCurrentPage($page) {
        $this->currentPage = max(1, intval($page)); // Minimum page 1
        return $this;
    }
    private function hasScope($scope){
        return $this->B24 && in_array($scope, $this->currentScope);
    }
    public function getContactMailActivities() {
        try {
            if(!$this->hasScope('crm')){
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            }

            // Calculer l'offset pour la pagination
            $start = ($this->currentPage - 1) * $this->itemsPerPage;
            
            $params = [
                'filter' => [
                    'PROVIDER_ID' => 'CRM_EMAIL',
                    'PROVIDER_TYPE_ID' => 'EMAIL',
                    'OWNER_ID' => 3,
                    'OWNER_TYPE_ID' => 1,
                ],
                'select' => [
                    'ID', 'SUBJECT', 'CREATED', 'LAST_UPDATED', 
                    'START_TIME', 'END_TIME', 'COMPLETED', 
                    'RESPONSIBLE_ID', 'DESCRIPTION'
                ],
                'order' => ['CREATED' => 'DESC'],
                'start' => $start,
                'limit' => $this->itemsPerPage
            ];

            // Récupérer le nombre total d'activités
            $totalCountParams = $params;
            unset($totalCountParams['start'], $totalCountParams['limit']);
            $totalCountResult = $this->B24->core->call('crm.activity.list', $totalCountParams)->getResponseData()->getResult();
            $totalCount = count($totalCountResult);

            // Récupérer les activités pour la page courante
            $result = $this->B24->core->call('crm.activity.list', $params)
                ->getResponseData()->getResult();

            $this->NSContactMailActivityCollection->activities = $result;
            $this->NSContactMailActivityCollection->pagination = [
                'total' => $totalCount,
                'itemsPerPage' => $this->itemsPerPage,
                'currentPage' => $this->currentPage,
                'totalPages' => ceil($totalCount / $this->itemsPerPage)
            ];
            
        } catch (Exception $e) {
            $this->log($e, 'Erreur lors de la récupération des activités mail');
            $this->NSContactMailActivityCollection->activities = [];
            $this->NSContactMailActivityCollection->pagination = [
                'total' => 0,
                'itemsPerPage' => $this->itemsPerPage,
                'currentPage' => $this->currentPage,
                'totalPages' => 0
            ];
        }
 
        return $this;
    }

    // Méthode pour obtenir les scopes manquants
    public function getMissingScopes() {
        return array_diff($this->requiredScopes, $this->currentScope);
    }

    
    private function log($e,$message='Erreur lors de la récupération'){
        echo'<pre>';
        var_dump($e);
        echo'</pre>';
        error_log($message .': '.$e->getMessage(),1,destination:__DIR__.'/error.log');
    }

    private function notify($message){
        return $this->B24 && in_array($scope, $this->currentScope);
    }
    public function getResponsible($userId){
      
        try{
            if (!is_numeric($userId) || !$this->hasScope('user') ||!$this->NSContactMailActivityCollection->responsible = $this->B24?->core?->call('user.get', [
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

    public function renderMailActivitiesList() {
            $activities = $this->NSContactMailActivityCollection->activities;
            $responsibles=[];
            foreach($activities as $key => $activity){
                $this->getResponsible($activity['RESPONSIBLE_ID']);
                $activities[$key]['responsible']=$this->NSContactMailActivityCollection->responsible[0]??null;
                $responsibles[]=$this->NSContactMailActivityCollection->responsible[0]??null;
            }
            $errorMessages=$this->errorMessages;
            $NSContactMailActivityCollection= $this->NSContactMailActivityCollection;
            include dirname(__FILE__) . '/template.php';
    }
    public function getCollection(){
        return $this->NSContactMailActivityCollection;
    }
}

// Si le script est appelé directement
if (isset($_GET['ajax']) && $_GET['ajax'] == 'y') {
    $mailActivities = new NSContactMailActivity();
    $mailActivities->renderMailActivitiesList();
}
?>