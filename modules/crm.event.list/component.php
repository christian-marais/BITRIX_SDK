<?php
declare(strict_types=1);
namespace NS2B;
use \Exception;
require_once dirname(__DIR__, 2) . '/modules/base/CrmActivity.php';

class NSContactEventActivity extends CrmActivity {
    
    public function getActivities() {
        try {
            if(!$this->hasScope('crm')){
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            }

            // Calculer l'offset pour la pagination
            $start = ($this->currentPage - 1) * $this->itemsPerPage;
            
            $params = [
                'filter' => [
                    'PROVIDER_ID' => 'CRM_TODO',
                    'PROVIDER_TYPE_ID' => 'TODO',
                    'OWNER_ID' => 3,
                    'OWNER_TYPE_ID' => 1,
                ],
                'select' => [
                    'ID', 'SUBJECT', 'CREATED', 'LAST_UPDATED', 
                    'START_TIME', 'END_TIME', 'COMPLETED', 
                    'RESPONSIBLE_ID','DESCRIPTION','SETTINGS','LOCATION',
                ],
                'order' => ['CREATED' => 'DESC'],
                'start' => $start,
                'limit' => $this->itemsPerPage
            ];

            // Récupérer le nombre total d'activités
            $totalCountParams = $params;
            unset($totalCountParams['start'], $totalCountParams['limit']);
            $totalCount = $this->B24->core->call('crm.activity.list', $totalCountParams)->getResponseData()->getPagination()->getTotal();//->getPagination()->getNextItem();
           
            // Récupérer les activités pour la page courante
            $result = $this->B24->core->call('crm.activity.list', $params)
                ->getResponseData()->getResult();
              
            $this->activityCollection->activities = $result;
            $this->activityCollection->pagination = [
                'total' => $totalCount,
                'itemsPerPage' => $this->itemsPerPage,
                'currentPage' => $this->currentPage,
                'totalPages' => ceil($totalCount / $this->itemsPerPage)
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
            $activities = $this->activityCollection->activities;
            $responsibles=[];
          
            foreach($activities as $key => $activity){
                
                if(isset($activity['SETTINGS']['USERS'])){
                    foreach($activity['SETTINGS']['USERS'] as $collaboratorId){
                        $this->getResponsible($collaboratorId);
                        $activities[$key]['COWORKERS'][]=$this->activityCollection->responsible[0];
                    }
                }
                $this->getResponsible($activity['RESPONSIBLE_ID']);
                unset($activities[$key]['SETTINGS']);
                $activities[$key]['responsible']=$this->activityCollection->responsible[0]??null;
                $responsibles[]=$this->activityCollection->responsible[0]??null;
            }
            

            $errorMessages=$this->errorMessages;
            $activityCollection= $this->activityCollection;
            include dirname(__FILE__) . '/template.php';
    }
}

   



// Si le script est appelé directement
// if (isset($_GET['ajax']) && $_GET['ajax'] == 'y') {
//     $mailActivities = new NSContactEventActivity();
//     $mailActivities->renderMailActivitiesList();
// }
?>