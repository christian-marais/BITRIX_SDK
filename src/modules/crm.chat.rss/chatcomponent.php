<?php
declare(strict_types=1);
namespace NS2B\SDK\MODULES\CRM\CHAT\RSS;
use \Exception;
use NS2B\SDK\MODULES\BASE\Base;


class ChatComponent extends Base{
    
    public function __construct(
        private $chatCollection =new \stdClass,
        protected $errorMessages=[],
        
    ){
        parent::__construct();
        $this->chatCollection->rss=[
            "cert"=>"https://www.cert.ssi.gouv.fr/alerte/feed/",
            "sfr"=>"https://www.sfr.fr/alerte/rss/",
            "france-interieur"=>"https://www.franceinterieur.fr/alerte/rss/",
            "gouv.fr"=>"https://www.gouv.fr/alerte/rss/"
        ];
    }
    

    public function getActivities() {
        try {
            if(!$this->hasScope('crm')){
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            }

            // Récupérer d'abord le nombre total d'activités
            $countParams = [
                'filter' => [
                    'PROVIDER_ID' => 'CRM_EMAIL',
                    'PROVIDER_TYPE_ID' => 'EMAIL',
                    'OWNER_ID' => 3,
                    'OWNER_TYPE_ID' => 1,
                ]
            ];
            $_GET['subject'] ? $countParams['filter']['%SUBJECT'] = htmlspecialchars($_GET['subject']) : '';
            $_GET['startDate'] ? $countParams['filter']['>=START_TIME'] = htmlspecialchars($_GET['startDate']) : '';
            $_GET['endDate'] ? $countParams['filter']['<=END_TIME'] = htmlspecialchars($_GET['endDate']) : '';
            $_GET['completed'] ? $countParams['filter']['COMPLETED'] = htmlspecialchars($_GET['completed']) : '';
            // Récupérer le nombre total
            $totalCount = $this->B24
                ->core
                ->call('crm.activity.list', $countParams)
                ->getResponseData()
                ->getPagination()
                ->getTotal();

            // Calculer l'offset pour la pagination
            $start = ($this->currentPage - 1) * $this->itemsPerPage;
            
            // Récupérer les activités pour la page courante avec la limite correcte
            $params = [
                'filter' => $countParams['filter'],
                'select' => [
                    'ID', 'SUBJECT', 'CREATED', 'LAST_UPDATED', 
                    'START_TIME', 'END_TIME', 'COMPLETED', 
                    'RESPONSIBLE_ID', 'DESCRIPTION'
                ],
                'order' => ['CREATED' => 'DESC'],
                'start' => $start,
                'limit' => intval($this->itemsPerPage)
            ];

            $result = $this->B24
                ->core
                ->call('crm.activity.list', $params)
                ->getResponseData()
                ->getResult();

            $this->activityCollection->activities = $result;
            $this->activityCollection->pagination = [
                'total' => $totalCount,
                'itemsPerPage' => intval($this->itemsPerPage),
                'currentPage' => intval($this->currentPage),
                'total' => $totalCount,
                'itemsPerPage' => intval($this->itemsPerPage),
                'currentPage' => intval($this->currentPage),
                'totalPages' => max(1, ceil($totalCount / $this->itemsPerPage))
            ];
            
        } catch (Exception $e) {
            $this->log($e, 'Erreur lors de la récupération des activités mail');
            $this->activityCollection->activities = [];
            $this->activityCollection->pagination = [
                'total' => 0,
                'itemsPerPage' => intval($this->itemsPerPage),
                'currentPage' => intval($this->currentPage),
                'totalPages' => 0
            ];
        }
 
        return $this;
    }


    public function renderActivitiesList(){
            $activities = $this->activityCollection->activities;
            $responsibles=[];
            foreach($activities as $key => $activity){
                $this->getResponsible($activity['RESPONSIBLE_ID']);
                $activities[$key]['responsible']=$this->activityCollection->responsible[0]??null;
                $responsibles[]=$this->activityCollection->responsible[0]??null;
            }
            $errorMessages=$this->errorMessages;
            $activityCollection= $this->activityCollection;
            include dirname(__FILE__) . '/template.php';
    }

    public function getCollection(){
        return $this->chatCollection;
    }
    public function getRss($url=null){
        if(empty($url??=$this->chatCollection->rss['cert'])) return null;
        $content=file_get_contents($url);
        $xml=new \SimpleXMLElement($content);
        return $xml;
    }
  
}

// Si le script est appelé directement
// if (isset($_GET['ajax']) && $_GET['ajax'] == 'y') {
//     $mailActivities = new NSContactMailActivity();
//     $mailActivities->renderActivitiesList();
// }
?>