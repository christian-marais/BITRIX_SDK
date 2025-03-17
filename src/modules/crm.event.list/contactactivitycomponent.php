<?php
declare(strict_types=1);
namespace NS2B\SDK\MODULES\CRM\EVENT\LIST;
use NS2B\SDK\MODULES\BASE\CrmActivity;
use \Exception;

class ContactActivityComponent extends CrmActivity {
    
    private $start;
    public function getActivities() {
        try {
            if(!$this->hasScope('crm'))
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            
            $arParams = [
               'completed','responsible','type','priority','location','collaborators','subject'
            ];

            // Construire le tableau de filtres
            $countParams = [
                'filter' => [
                    'PROVIDER_ID' => 'CRM_TODO',
                    'PROVIDER_TYPE_ID' => 'TODO',
                    'OWNER_ID' => 3,
                    'OWNER_TYPE_ID' => 1,
                ]
            ];
            
            // Ajouter les filtres dynamiques

            foreach ($arParams as $param) {
                if (isset($_GET[$param])) {
                    $$param = isset($_GET[$param])? htmlentities($_GET[$param]): [];
                    (!empty($$param) && ($param=='type' || $param=='responsible'))?
                    $countParams['filter'][strtoupper($param).'_ID'] = $$param: 
                    (($param=='subject'&& !empty($_GET[$param]))?
                        $countParams['filter']['%'.strtoupper($param)]=$_GET[$param]:
                        $countParams['filter'][strtoupper($param)]= $$param);
                    unset($countParams['filter']['SUBJECT']);
                }
          
            }
           
            $startDate = isset($_GET['startDate']) ?htmlentities($_GET['startDate']) : null;
            $startDate?$countParams['filter']['>=CREATED'] = $startDate:"";
            $endDate = isset($_GET['endDate']) ? htmlentities($_GET['endDate']) : null;
            $endDate?$countParams['filter']['<=CREATED'] = $endDate:"";
            // Récupérer le nombre total
            $totalCount = $this->B24->core
                ->call('crm.activity.list', $countParams)
                ->getResponseData()
                ->getPagination()
                ->getTotal();
            
            // Calculer l'offset pour la pagination
            $this->start = ($this->currentPage-1) * $this->itemsPerPage;
            
            // Récupérer les activités pour la page courante avec la limite correcte
            $params = [
                'filter' => $countParams['filter'],
                'select' => [
                    'ID', 'SUBJECT', 'CREATED', 'LAST_UPDATED', 
                    'START_TIME', 'END_TIME', 'COMPLETED', 
                    'RESPONSIBLE_ID', 'DESCRIPTION', 'SETTINGS', 'LOCATION'
                ],
                'order' => ['CREATED' => 'DESC'],
                'start' =>0,
                'limit' => $this->itemsPerPage
            ];

            // echo'<pre>';
            // var_dump($params);echo'</pre>';die();
            do{
                $response = $this->B24
                    ->core
                    ->call('crm.activity.list', $params)
                    ->getResponseData();
                $result = array_merge($result??[],$response
                    ->getResult());
            }while($params['start']=$response
                    ->getPagination()
                    ->getNextItem()
            );
            

            $this->activityCollection->activities = $result;
            $this->activityCollection->pagination = [
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
                'itemsPerPage' => $this->itemsPerPage,
                'currentPage' => $this->currentPage,
                'totalPages' => 0
            ];
        }
 
        return $this;
    }

    

    public function renderActivitiesList() {
            $activityCollection= $this->activityCollection;
            $responsibles=[];
            $coworkers=[];
            foreach($activityCollection->activities as $key => $activity){
                
                if(isset($activity['SETTINGS']['USERS'])){
                    foreach($activity['SETTINGS']['USERS'] as $collaboratorId){
                        if($collaboratorId!==(int)$activity['RESPONSIBLE_ID']){
                            $this->getResponsible($collaboratorId);
                            $activityCollection->activities[$key]['COWORKERS'][]=$this->activityCollection->responsible[0];
                            $coworkers[$collaboratorId]=$this->activityCollection->responsible[0];
                        }
                    }
                }
                
                $this->getResponsible($activity['RESPONSIBLE_ID']);
                unset($activities[$key]['SETTINGS']);
                $activityCollection->activities[$key]['responsible']=$this->activityCollection->responsible[0]??null;
                $responsibles[$activity['RESPONSIBLE_ID']]=$this->activityCollection->responsible[0]??null;
            }
            $activities=array_slice($activityCollection->activities,$this->start,($this->start+$this->itemsPerPage));
            $errorMessages=$this->errorMessages;
            include dirname(__FILE__) . '/template.php';
    }
}

   



// Si le script est appelé directement
// if (isset($_GET['ajax']) && $_GET['ajax'] == 'y') {
//     $mailActivities = new NSContactEventActivity();
//     $mailActivities->renderMailActivitiesList();
// }
?>